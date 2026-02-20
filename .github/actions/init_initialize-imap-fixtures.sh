#!/bin/bash -e

ROOT_DIR=$(readlink -f "$(dirname $0)/../..")

echo "Initialize email fixtures"
docker-compose exec -T --user root dovecot doveadm expunge -u testuser mailbox 'INBOX' all
docker-compose exec -T --user root dovecot doveadm purge -u testuser
for f in "$ROOT_DIR"/tests/emails-tests/*.eml; do
  cat $f | docker-compose exec -T --user testuser dovecot getmail_maildir /home/testuser/Maildir/
done
