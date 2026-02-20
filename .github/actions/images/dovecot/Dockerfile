FROM debian:bookworm-slim

ENV DEBIAN_FRONTEND=noninteractive

RUN apt-get update \
 && apt-get install -y --no-install-recommends \
    dovecot-core \
    dovecot-imapd \
    dovecot-pop3d \
 && rm -rf /var/lib/apt/lists/*

RUN useradd --create-home --home-dir /home/itsm --shell /bin/bash itsm \
 && useradd --create-home --home-dir /home/testuser --shell /bin/bash testuser \
 && echo 'itsm:applesauce' | chpasswd \
 && echo 'testuser:applesauce' | chpasswd \
 && mkdir -p /home/itsm/Maildir/{cur,new,tmp} /home/testuser/Maildir/{cur,new,tmp} \
 && chown -R itsm:itsm /home/itsm \
 && chown -R testuser:testuser /home/testuser

RUN sed -ri 's|^#?listen =.*$|listen = *, ::|' /etc/dovecot/dovecot.conf \
 && sed -ri 's|^#?mail_location =.*$|mail_location = maildir:~/Maildir|' /etc/dovecot/conf.d/10-mail.conf \
 && sed -ri 's|^#?disable_plaintext_auth =.*$|disable_plaintext_auth = no|' /etc/dovecot/conf.d/10-auth.conf \
 && sed -ri 's|^auth_mechanisms =.*$|auth_mechanisms = plain login|' /etc/dovecot/conf.d/10-auth.conf \
 && sed -ri 's|^ssl =.*$|ssl = no|' /etc/dovecot/conf.d/10-ssl.conf

COPY .github/actions/images/dovecot/getmail_maildir /usr/local/bin/getmail_maildir
RUN chmod +x /usr/local/bin/getmail_maildir

EXPOSE 143

CMD ["dovecot", "-F"]
