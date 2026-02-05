#!/bin/bash
# DI Form Buddy - Environment Setup
# Run once: ./setup.sh
# This exports required env vars to your shell profile.

set -e

SHELL_RC="$HOME/.zshrc"
if [ ! -f "$SHELL_RC" ]; then
  SHELL_RC="$HOME/.bashrc"
fi

echo "DI Form Buddy - Setup"
echo "====================="
echo ""

# Check if already configured
if grep -q "DI_FORM_BUDDY_SECRET" "$SHELL_RC" 2>/dev/null; then
  echo "Found existing DI Form Buddy config in $SHELL_RC"
  echo "To reconfigure, remove the '# DI Form Buddy' block from $SHELL_RC and re-run."
  exit 0
fi

# SSH credentials
read -p "SSH username (e.g., nhart): " ssh_user
read -sp "SSH password: " ssh_pass
echo ""

# Form Buddy secret
echo ""
echo "Generating HMAC secret..."
fb_secret=$(php -r "echo bin2hex(random_bytes(32));")
echo "Generated: ${fb_secret:0:8}..."

# Write to shell profile
cat >> "$SHELL_RC" << EOF

# DI Form Buddy
export SSH_USERNAME="${ssh_user}"
export SSH_PASSWORD="${ssh_pass}"
export SSHPASS="${ssh_pass}"  # sshpass -e expects this
export DI_FORM_BUDDY_SECRET="${fb_secret}"
EOF

echo ""
echo "Configuration written to $SHELL_RC"
echo ""
echo "Run: source $SHELL_RC"
echo ""
echo "Then verify:"
echo "  echo \$SSH_USERNAME     # should show: ${ssh_user}"
echo "  echo \$DI_FORM_BUDDY_SECRET  # should show: ${fb_secret:0:8}..."
echo ""
echo "Done."
