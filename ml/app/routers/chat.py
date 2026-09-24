from __future__ import annotations

import logging
from typing import Optional

from fastapi import APIRouter, HTTPException

from app.db.queries import fetch_user_financial_aggregation, log_llm_call
from app.models.schemas import ChatRequest, ChatResponse
from app.services.llm_service import llm

logger = logging.getLogger(__name__)

router = APIRouter(prefix="/api/v1", tags=["AI Assistant"])


@router.post("/chat", response_model=ChatResponse)
async def chat_with_assistant(request: ChatRequest) -> ChatResponse:
    """Conversational financial assistant endpoint powered by local/external LLM.

    Injects user financial summary into the system context to answer questions in
    plain English or Sheng.
    """
    user_id = request.user_id.strip()
    if not user_id:
        raise HTTPException(status_code=400, detail="user_id is required")

    user_query = request.message.strip()
    if not user_query:
        raise HTTPException(status_code=400, detail="message cannot be empty")

    # Fetch financial snapshot from DB
    try:
        agg = await fetch_user_financial_aggregation(user_id)
    except Exception as e:
        logger.warning(f"Failed to fetch financial aggregation for user {user_id}: {e}")
        agg = {
            "transaction_count": 0,
            "total_sent_money": 0.0,
            "total_received_money": 0.0,
            "total_transaction_volume": 0.0,
            "transaction_types": {},
        }

    tx_count = agg.get("transaction_count", 0)
    sent = agg.get("total_sent_money", 0.0)
    received = agg.get("total_received_money", 0.0)
    volume = agg.get("total_transaction_volume", 0.0)
    types = agg.get("transaction_types", {})

    types_summary = ", ".join(f"{k}: {v}" for k, v in types.items()) if types else "None recorded"

    system_prompt = (
        "You are 'M-Pesa Smart Advisor', an expert personal financial assistant and wealth advisor for Kenyan users.\n"
        "You have direct access to the user's analyzed M-Pesa transaction history:\n"
        f"- Total Outflow (Money Sent): KES {sent:,.2f}\n"
        f"- Total Inflow (Money Received): KES {received:,.2f}\n"
        f"- Total Transaction Volume: KES {volume:,.2f}\n"
        f"- Total Analyzed Transactions: {tx_count}\n"
        f"- Transaction Type Distribution: {types_summary}\n\n"
        "Instructions:\n"
        "1. Answer user questions supportively, concisely, and accurately based on their numbers.\n"
        "2. You comfortably understand and reply in Kenyan English and Sheng (e.g., pesa, ganji, chapaa, kutuma, fuliza).\n"
        "3. Provide realistic financial coaching on savings, budgeting, and debt control (Fuliza/M-Shwari).\n"
        "4. Keep answers focused, well-formatted, and under 180 words unless the user asks for a detailed breakdown."
    )

    messages = [{"role": "system", "content": system_prompt}]

    # Include recent history (capped to last 8 turns)
    for msg in request.history[-8:]:
        role = msg.role if msg.role in ("user", "assistant") else "user"
        messages.append({"role": role, "content": msg.content})

    # Add current query
    messages.append({"role": "user", "content": user_query})

    try:
        call_res = await llm.chat(messages)
    except Exception as e:
        logger.error(f"Chat completion failed: {e}", exc_info=True)
        raise HTTPException(status_code=502, detail=f"LLM inference error: {str(e)}")

    tokens_used = (call_res.reply_tokens or 0) + (call_res.prompt_tokens or 0)

    try:
        await log_llm_call(
            call_type="chat",
            model=call_res.model,
            provider=call_res.provider,
            latency_ms=call_res.latency_ms,
            batch_size=1,
            status="fallback" if call_res.used_fallback else "ok",
            prompt_tokens=call_res.prompt_tokens,
            reply_tokens=call_res.reply_tokens,
        )
    except Exception as e:
        logger.warning(f"Could not log chat LLM call: {e}")

    return ChatResponse(
        reply=call_res.content,
        latency_ms=call_res.latency_ms,
        tokens_used=tokens_used,
    )
