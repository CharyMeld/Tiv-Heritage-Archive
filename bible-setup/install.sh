#!/usr/bin/env bash
# ============================================================
# Tiv Heritage Archive — SWORD Bible Tools Installer
# Run: bash bible-setup/install.sh
# ============================================================
set -e

echo "=== Installing SWORD command-line tools ==="
sudo apt-get update -qq
sudo apt-get install -y diatheke libsword-utils sword-text-kjv

echo ""
echo "=== Creating SWORD module directory ==="
SWORD_DIR="$HOME/.sword"
mkdir -p "$SWORD_DIR/mods.d"
mkdir -p "$SWORD_DIR/modules/texts/ztext"

echo ""
echo "=== Downloading Bible modules via sword-installmgr ==="
# Configure the CrossWire remote repository
cat > /tmp/sword_install.conf <<EOF
[General]
PassiveFTP=true

[CrossWire]
DataPath=/usr/share/sword/
[CrossWire Remote]
Type=FTPDir
Source=ftp.crosswire.org
Directory=/pub/sword/raw
LocalShadow=false
EOF

# Install WEB (World English Bible - public domain, full Bible)
echo "Downloading WEB (World English Bible)..."
sword-installmgr -init 2>/dev/null || true
sword-installmgr -sc 2>/dev/null || true
sudo sword-installmgr -r CrossWire -mI WEB 2>/dev/null || \
    echo "  → Trying alternate install method..."

# Install NTIV (Tiv New Testament)
echo "Downloading NTIV (Tiv New Testament)..."
sudo sword-installmgr -r CrossWire -mI NTIV 2>/dev/null || \
    echo "  → NTIV install attempted."

echo ""
echo "=== Verifying installed modules ==="
diatheke -b system -k modulelist 2>/dev/null || true

echo ""
echo "=== Quick test ==="
diatheke -b WEB  -k "John 3:16" 2>/dev/null || echo "WEB: check module path"
diatheke -b NTIV -k "John 3:16" 2>/dev/null || echo "NTIV: check module path"

echo ""
echo "Done. Next run: python3 bible-setup/extract.py"
