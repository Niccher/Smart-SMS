#!/usr/bin/env python3
"""
Synthetic Financial SMS Generator (generate_synthetic_sms.py)

Generates realistic, mock Kenyan financial SMS messages for zero-privacy-leak
testing and development across M-Pesa, Commercial Banks, and Digital Lenders.

Usage:
    python3 scripts/generate_synthetic_sms.py --count 100 --output scratch/mock_sms.json
    python3 scripts/generate_synthetic_sms.py --count 20 --format txt
"""

import argparse
import json
import random
import string
import sys
from datetime import datetime, timedelta
from pathlib import Path

KENYAN_FIRST_NAMES = [
    "John", "Jane", "David", "Mary", "Brian", "Faith", "Kevin", "Grace",
    "Dennis", "Mercy", "Peter", "Esther", "James", "Alice", "Samuel", "Hellen"
]

KENYAN_SUR_NAMES = [
    "Kiprono", "Wanjiku", "Otieno", "Mutua", "Kamau", "Chebet", "Mwangi",
    "Ochieng", "Njoroge", "Kariuki", "Koech", "Achieng", "Wanyonyi", "Musyoka"
]

BUSINESSES = [
    ("NAIVAS SUPERMARKET", "Buy Goods"),
    ("QUICKMART TOM MBOYA", "Buy Goods"),
    ("JAVA HOUSE UPPERHILL", "Buy Goods"),
    ("CARREFOUR SARIT", "Buy Goods"),
    ("TOTAL ENERGIES WESTLANDS", "Buy Goods"),
    ("CLEAN SHELF SUPERMARKET", "Buy Goods"),
    ("KPLC PREPAID", "Paybill", "888880"),
    ("NAIROBI WATER", "Paybill", "444400"),
    ("ZUKU FIBER", "Paybill", "320320"),
    ("SAFARICOM HOME FIBRE", "Paybill", "150500"),
    ("EQUITY PAYBILL", "Paybill", "247247"),
    ("KCB PAYBILL", "Paybill", "522522"),
]

def random_code(prefix="QA"):
    digits_and_letters = ''.join(random.choices(string.ascii_uppercase + string.digits, k=8))
    return f"{prefix}{digits_and_letters}"

def random_phone():
    prefixes = ["0712", "0722", "0720", "0790", "0740", "0110", "0701", "0728"]
    return f"{random.choice(prefixes)}{random.randint(100000, 999999)}"

def random_name():
    return f"{random.choice(KENYAN_FIRST_NAMES)} {random.choice(KENYAN_SUR_NAMES)}".upper()

def generate_mpesa_send(dt):
    code = random_code("QA")
    amt = round(random.uniform(100, 15000), 2)
    name = random_name()
    phone = random_phone()
    bal = round(random.uniform(500, 50000), 2)
    cost = 0.00 if amt <= 100 else (7.00 if amt <= 500 else (13.00 if amt <= 1000 else 25.00))
    time_str = dt.strftime("%-I:%M %p")
    date_str = dt.strftime("%-d/%-m/%y")
    return {
        "sender": "MPESA",
        "date": dt.isoformat(),
        "body": f"{code} Confirmed. Ksh{amt:,.2f} sent to {name} {phone} on {date_str} at {time_str}. New M-PESA balance is Ksh{bal:,.2f}. Transaction cost, Ksh{cost:,.2f}."
    }

def generate_mpesa_paybill(dt):
    code = random_code("QA")
    biz = random.choice([b for b in BUSINESSES if len(b) == 3])
    amt = round(random.uniform(250, 8500), 2)
    acc = str(random.randint(100000000, 999999999))
    bal = round(random.uniform(300, 35000), 2)
    time_str = dt.strftime("%-I:%M %p")
    date_str = dt.strftime("%-d/%-m/%y")
    return {
        "sender": "MPESA",
        "date": dt.isoformat(),
        "body": f"{code} Confirmed. Ksh{amt:,.2f} sent to {biz[0]} for account {acc} on {date_str} at {time_str}. New M-PESA balance is Ksh{bal:,.2f}. Transaction cost, Ksh0.00."
    }

def generate_mpesa_till(dt):
    code = random_code("QA")
    biz = random.choice([b for b in BUSINESSES if len(b) == 2])
    amt = round(random.uniform(150, 6200), 2)
    bal = round(random.uniform(200, 25000), 2)
    time_str = dt.strftime("%-I:%M %p")
    date_str = dt.strftime("%-d/%-m/%y")
    return {
        "sender": "MPESA",
        "date": dt.isoformat(),
        "body": f"{code} Confirmed. Ksh{amt:,.2f} paid to {biz[0]} on {date_str} at {time_str}. New M-PESA balance is Ksh{bal:,.2f}. Transaction cost, Ksh0.00."
    }

def generate_fuliza(dt):
    code = random_code("FL")
    amt = round(random.uniform(200, 3500), 2)
    fee = round(random.uniform(5, 25), 2)
    outstanding = round(amt + fee, 2)
    return {
        "sender": "Fuliza M-PESA",
        "date": dt.isoformat(),
        "body": f"Fuliza M-PESA: {code} Outstanding amount is Ksh {outstanding:,.2f} due on {(dt + timedelta(days=1)).strftime('%d/%m/%y')}. Daily maintenance fee of Ksh {fee:,.2f} applied."
    }

def generate_bank_alert(dt):
    banks = [
        ("EQUITY", "Dear Customer, your A/C 01202998877 has been debited with KES {amt:,.2f} on {date_str} at {time_str}. Ref: {code}. Available balance is KES {bal:,.2f}."),
        ("KCB", "Your KCB A/C *4321 has been credited with KES {amt:,.2f} from {name} on {date_str}. Ref {code}. Available Bal: KES {bal:,.2f}."),
        ("COOP_BANK", "Co-op Bank Alert: Paid KES {amt:,.2f} via Mobile Banking on {date_str}. Ref:{code}. Clear Bal: KES {bal:,.2f}."),
        ("NCBA", "NCBA Loop: Debit of KES {amt:,.2f} processed at {date_str} {time_str}. Ref: {code}. Ledger Bal: KES {bal:,.2f}.")
    ]
    bank_sender, template = random.choice(banks)
    amt = round(random.uniform(500, 45000), 2)
    bal = round(random.uniform(1000, 120000), 2)
    code = random_code("BK")
    name = random_name()
    date_str = dt.strftime("%d-%m-%Y")
    time_str = dt.strftime("%H:%M:%S")
    body = template.format(amt=amt, bal=bal, code=code, name=name, date_str=date_str, time_str=time_str)
    return {
        "sender": bank_sender,
        "date": dt.isoformat(),
        "body": body
    }

def generate_digital_loan(dt):
    lenders = ["HUSTLER FUND", "TALA", "BRANCH"]
    lender = random.choice(lenders)
    amt = round(random.uniform(500, 5000), 2)
    code = random_code("LN")
    due_date = (dt + timedelta(days=14)).strftime("%d/%m/%Y")
    return {
        "sender": lender.replace(" ", ""),
        "date": dt.isoformat(),
        "body": f"{lender}: You have received Ksh {amt:,.2f} on {dt.strftime('%d/%m/%Y')}. Ref: {code}. Total repayable by {due_date} is Ksh {round(amt * 1.08, 2):,.2f}."
    }

GENERATORS = [
    (generate_mpesa_send, 35),
    (generate_mpesa_paybill, 20),
    (generate_mpesa_till, 20),
    (generate_fuliza, 10),
    (generate_bank_alert, 10),
    (generate_digital_loan, 5)
]

def generate_dataset(count=100):
    weights = [weight for _, weight in GENERATORS]
    funcs = [func for func, _ in GENERATORS]
    now = datetime.now()
    results = []

    for i in range(count):
        chosen_func = random.choices(funcs, weights=weights, k=1)[0]
        # Spread dates over past 90 days
        delta_days = random.randint(0, 90)
        delta_seconds = random.randint(0, 86400)
        tx_dt = now - timedelta(days=delta_days, seconds=delta_seconds)
        results.append(chosen_func(tx_dt))

    # Sort chronologically
    results.sort(key=lambda x: x["date"])
    return results

def main():
    parser = argparse.ArgumentParser(description="Generate synthetic financial SMS messages.")
    parser.add_argument("--count", type=int, default=100, help="Number of messages to generate (default: 100)")
    parser.add_argument("--format", choices=["json", "txt"], default="json", help="Output format (default: json)")
    parser.add_argument("--output", type=str, default="", help="File path to save output (optional)")

    args = parser.parse_args()
    dataset = generate_dataset(args.count)

    if args.format == "json":
        output_str = json.dumps(dataset, indent=2)
    else:
        output_str = "\n\n".join(f"[{item['sender']} | {item['date']}]\n{item['body']}" for item in dataset)

    if args.output:
        p = Path(args.output)
        p.parent.mkdir(parents=True, exist_ok=True)
        p.write_text(output_str, encoding="utf-8")
        print(f"✓ Successfully generated {args.count} synthetic financial SMS messages in: {args.output}")
    else:
        print(output_str)

if __name__ == "__main__":
    main()
