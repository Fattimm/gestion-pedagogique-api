import SwaggerUI from 'swagger-ui';
import 'swagger-ui/dist/swagger-ui.css';

SwaggerUI({
  url: '/api.yaml',  // Chemin vers votre fichier api.yaml
  dom_id: '#swagger-ui',
  deepLinking: true,
  presets: [
    SwaggerUI.presets.apis,
    SwaggerUI.SwaggerUIStandalonePreset
  ],
  layout: "BaseLayout",
  supportedSubmitMethods: ['get', 'post', 'put', 'delete', 'patch'],
});