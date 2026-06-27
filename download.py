import urllib.request
import os

url = "https://labs.nmgdigital.com/forge2-pulsedesk-starter-kit.zip"
req = urllib.request.Request(url, headers={'User-Agent': 'Mozilla/5.0'})
try:
    with urllib.request.urlopen(req) as response:
        content = response.read()
        with open("forge2-pulsedesk-starter-kit.zip", "wb") as f:
            f.write(content)
        print("Success, downloaded zip.")
except Exception as e:
    print("Error:", e)
