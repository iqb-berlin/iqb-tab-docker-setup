import { NestFactory } from '@nestjs/core';
import { AppModule } from './app.module';
import { WsAdapter } from '@nestjs/platform-ws';

async function bootstrap() {
  const app = await NestFactory.create(AppModule, {logger: ['warn', 'error']});
  app.useWebSocketAdapter(new WsAdapter(app));
  await app.listen(3000);
  console.log(`Broadcasting Service is running on: ${await app.getUrl()}`);
}
bootstrap();
