import requests
from datetime import datetime, date
from decimal import Decimal
import os
from dotenv import load_dotenv
load_dotenv()

EXTERNAL_INVOICE_API_KEY = os.getenv("EXTERNAL_INVOICE_API_KEY")
def send_to_laravel(json_results, endpoint_type):
    try:
        url = f"http://192.168.200.30/public/third-party-{endpoint_type}"
        headers = {"Content-Type": "application/json", "X-API-Key": EXTERNAL_INVOICE_API_KEY}

        for item in json_results:
            for key, value in item.items():
                if isinstance(value, (datetime, date)):
                    item[key] = value.strftime("%Y-%m-%d %H:%M:%S")
                elif isinstance(value, Decimal):
                    item[key] = float(value)

        response = requests.post(url, json=json_results, headers=headers, timeout=30)

        # Debug output
        print(f"Status: {response.status_code}")
        print(f"Body: {response.text[:500]}")  # First 500 chars

        return response.json() if response.text.strip() else None
    except requests.exceptions.ConnectionError as e:
        print(f"Connection failed: {e}")
        return None
    except Exception as e:
        print(f"Error: {e}")
        return None


# Test
if __name__ == "__main__":
    test_data = [
        {
            "kolicina": 1,
            "cena": 100,
            "naziv": "Test",
            "jm": "kom",
            "gotovina": 100,
            "kartica": 0,
            "prenosnaracun": 0,
            "datum": "2026-01-26 14:30:00",
            "brojracuna": "PY-TEST-001",
            "sto": "Sto 1",
            "porudzbinaid": 222,
        }
    ]
    result = send_to_laravel(test_data, "invoice")
    print(f"Result: {result}")
