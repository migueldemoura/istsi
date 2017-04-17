FROM debian:jessie

RUN apt-get update && \
    apt-get install -y --no-install-recommends mysql-client cron gettext-base
RUN mkdir /backup

COPY main.sh /main.sh
COPY backup.sh /backup.sh
COPY restore.sh /restore.sh
RUN chmod +x /main.sh

CMD ["/main.sh"]
