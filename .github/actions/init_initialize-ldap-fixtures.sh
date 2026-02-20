#!/bin/bash -e

ROOT_DIR=$(readlink -f "$(dirname $0)/../..")
LDAP_URI=${LDAP_URI:-ldap://openldap:389}
LDAP_BIND_DN=${LDAP_BIND_DN:-cn=admin,dc=glpi,dc=org}
LDAP_BIND_PW=${LDAP_BIND_PW:-insecure}
LDAP_BASE_DN=${LDAP_BASE_DN:-dc=glpi,dc=org}

echo "Initialize LDAP fixtures"
LDAP_CHILD_DNS=$(docker-compose exec -T openldap ldapsearch -LLL -x -H "$LDAP_URI" -D "$LDAP_BIND_DN" -w "$LDAP_BIND_PW" -b "$LDAP_BASE_DN" -s sub "(objectClass=*)" dn \
  | sed -n 's/^dn: //p' \
  | grep -vi "^${LDAP_BASE_DN}$" \
  | tac || true)

if [ -n "$LDAP_CHILD_DNS" ]; then
  echo "$LDAP_CHILD_DNS" | docker-compose exec -T openldap ldapdelete -x -H "$LDAP_URI" -D "$LDAP_BIND_DN" -w "$LDAP_BIND_PW" -c
fi

for f in "$ROOT_DIR"/tests/LDAP/ldif/*.ldif; do
  awk '
    BEGIN {
      RS = "";
      ORS = "\n\n";
    }
    {
      dn = "";
      n = split($0, lines, "\n");
      for (i = 1; i <= n; i++) {
        if (lines[i] ~ /^dn:[[:space:]]*/) {
          dn = lines[i];
          break;
        }
      }

      if (dn != "dn: dc=glpi,dc=org" && dn != "dn: cn=admin,dc=glpi,dc=org" && dn != "dn: cn=admin, dc=glpi,dc=org") {
        print $0;
      }
    }
  ' "$f" | docker-compose exec -T openldap ldapadd -x -H "$LDAP_URI" -D "$LDAP_BIND_DN" -w "$LDAP_BIND_PW" -c
done
