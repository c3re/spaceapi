FROM debian:buster
RUN apt-get update && apt-get install -y nginx-light
ADD spaceapi.conf /etc/nginx/sites-available
RUN rm /etc/nginx/sites-enabled/*
RUN ln -s /etc/nginx/sites-available/spaceapi.conf /etc/nginx/sites-enabled/
EXPOSE 80
CMD ["nginx", "-g", "daemon off;"]
