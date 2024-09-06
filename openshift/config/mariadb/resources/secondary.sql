# We have to know name of sts (`db`) and
# service `db` in advance as an FQDN.
# No need to use master_port
CHANGE MASTER TO
  MASTER_HOST='db-0.db',
  MASTER_USER='repluser',
  MASTER_PASSWORD='replsecret',
  MASTER_CONNECT_RETRY=10;
  # MASTER_USE_GTID=current_pos,
  # MASTER_SSL=1,
  # MASTER_SSL_CA='ca.pem',
  # MASTER_SSL_CERT='client-cert.pem',
  # MASTER_SSL_KEY='client-key.pem';
  # MASTER_LOG_FILE='db-bin.000004',
  # MASTER_LOG_POS=547,
  MASTER_AUTO_POSITION=1;
  MASTER_CONNECT_RETRY=60;
FLUSH PRIVILEGES;
