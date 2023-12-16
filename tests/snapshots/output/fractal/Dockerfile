FROM node:20-bullseye-slim AS base

WORKDIR /app

RUN mkdir /node_modules \
  && chown node:node -R /app /node_modules

COPY --chown=node:node package*.json *yarn* /app

USER node

################################################################################

FROM base AS build

RUN yarn install --frozen-lockfile

COPY --chown=node:node . .

CMD ["bash"]
