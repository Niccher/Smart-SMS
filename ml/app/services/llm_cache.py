from __future__ import annotations

import hashlib
import json
import logging
from typing import Any, Optional

import redis.asyncio as aioredis
from app.config import settings

logger = logging.getLogger(__name__)


class LLMCache:
    def __init__(self):
        self._redis: Optional[aioredis.Redis] = None
        self._available: bool = True

    async def _get_client(self) -> Optional[aioredis.Redis]:
        if not self._available:
            return None
        if self._redis is None:
            try:
                self._redis = aioredis.Redis(
                    host=settings.redis_host,
                    port=settings.redis_port,
                    password=settings.redis_password or None,
                    decode_responses=True,
                    socket_connect_timeout=2.0,
                    socket_timeout=2.0,
                )
                await self._redis.ping()
                logger.info(f"Connected to Redis cache at {settings.redis_host}:{settings.redis_port}")
            except Exception as e:
                logger.warning(f"Redis cache unavailable ({e}), running without cache")
                self._available = False
                self._redis = None
        return self._redis

    def _hash_key(self, prefix: str, data: str) -> str:
        h = hashlib.sha256(data.encode("utf-8")).hexdigest()
        return f"llm_cache:{prefix}:{h}"

    async def get(self, prefix: str, key_content: str) -> Optional[dict[str, Any]]:
        try:
            client = await self._get_client()
            if not client:
                return None
            key = self._hash_key(prefix, key_content)
            cached = await client.get(key)
            if cached:
                return json.loads(cached)
        except Exception as e:
            logger.debug(f"Redis get failed: {e}")
        return None

    async def set(
        self, prefix: str, key_content: str, value: dict[str, Any], ttl_seconds: int = 3600
    ) -> None:
        try:
            client = await self._get_client()
            if not client:
                return
            key = self._hash_key(prefix, key_content)
            await client.set(key, json.dumps(value), ex=ttl_seconds)
        except Exception as e:
            logger.debug(f"Redis set failed: {e}")

    async def close(self) -> None:
        if self._redis:
            try:
                await self._redis.close()
            except Exception:
                pass
            self._redis = None


llm_cache = LLMCache()
