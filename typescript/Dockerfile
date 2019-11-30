FROM node:alpine AS build

WORKDIR /app

COPY api.ts api.ts
COPY package.json package.json
COPY yarn.lock yarn.lock
COPY tsconfig.json tsconfig.json

RUN yarn install
RUN yarn build

RUN yarn clean
RUN yarn install --production

FROM node:alpine

WORKDIR /app

COPY package.json package.json
COPY --from=build /app/node_modules node_modules
COPY --from=build /app/api.js api.js

CMD ["yarn", "start"]

EXPOSE 3000