#!/bin/bash -e

ROOT_DIR=$(readlink -f "$(dirname $0)/../..")

echo "Initialize email fixtures"
docker-compose exec -T --user root dovecot doveadm expunge -u itsm mailbox 'INBOX' all
docker-compose exec -T --user root dovecot doveadm purge -u itsm
for f in `ls $ROOT_DIR/tests/emails-tests/*.eml`; do
  cat $f | docker-compose exec -T --user itsm dovecot getmail_maildir /home/itsm/Maildir/
done
