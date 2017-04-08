FROM debian:jessie

RUN apt-get update && apt-get install -y cron
RUN mkdir /backup

COPY main.sh /main.sh
COPY backup.sh /backup.sh
COPY restore.sh /restore.sh
RUN cd / && chmod +x main.sh backup.sh restore.sh

CMD ["/main.sh"]
