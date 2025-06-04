# Use the official Node.js image.
FROM node:22-alpine

# Create and change to the app directory.
WORKDIR /app

# Install PM2 globally
RUN npm install pm2 -g

RUN npm install nodemon -g


# Document that the service listens on port 3000.
EXPOSE 5173

COPY docker/node/docker-entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh
ENTRYPOINT ["/entrypoint.sh"]
