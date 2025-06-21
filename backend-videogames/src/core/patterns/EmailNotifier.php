<?php
// src/core/patterns/EmailNotifier.php

include_once __DIR__ . '/Observer.php';
require_once __DIR__ . '/../../vendor/autoload.php';

class EmailNotifier implements Observer {
    
    public function update(Subject $subject) {
        $details = $subject->getPedidoDetails();
        
        // --- DEPURACIÓN ---
        // Vamos a registrar en los logs qué valores estamos leyendo realmente.
        // error_log("--- Inciando envío de correo para pedido: " . $details['pedido_id'] . " ---");
        // error_log("API Key leída: " . (getenv('SENDGRID_API_KEY') ? 'Encontrada (longitud: ' . strlen(getenv('SENDGRID_API_KEY')) . ')' : '¡NO ENCONTRADA!'));
        // error_log("Email Remitente leído: " . (getenv('SENDGRID_FROM_EMAIL') ?: '¡NO ENCONTRADO!'));
        // error_log("Nombre Remitente leído: " . (getenv('SENDGRID_FROM_NAME') ?: '¡NO ENCONTRADO!'));
        // --- FIN DE LA DEPURACIÓN ---

        $email = new \SendGrid\Mail\Mail(); 
        
        try {
            $from_email = getenv('SENDGRID_FROM_EMAIL');
            $from_name = getenv('SENDGRID_FROM_NAME');

            // Verificación para no enviar con datos vacíos
            if (empty($from_email) || empty($from_name) || empty(getenv('SENDGRID_API_KEY'))) {
                error_log("Error crítico: Faltan variables de entorno de SendGrid. El correo no se enviará.");
                return; // Detenemos la ejecución si las variables no están
            }

            $email->setFrom($from_email, $from_name);
            $email->setSubject("Confirmación de tu pedido #" . $details['pedido_id']);
            $email->addTo($details['usuario_email'], $details['usuario_nombre']);

            $plainTextContent = "¡Gracias por tu compra, " . $details['usuario_nombre'] . "!\n\n";
            $plainTextContent .= "Hemos recibido tu pedido #" . $details['pedido_id'] . " por un total de $" . $details['total'] . ".\n\n";
            $plainTextContent .= "Resumen del pedido:\n" . $details['resumen_pedido'];
            $email->addContent("text/plain", $plainTextContent);
            
            $htmlContent = "<h2>¡Gracias por tu compra, " . $details['usuario_nombre'] . "!</h2>";
            $htmlContent .= "<p>Hemos recibido tu pedido <strong>#" . $details['pedido_id'] . "</strong> por un total de <strong>$" . $details['total'] . "</strong>.</p>";
            $htmlContent .= "<h4>Resumen del pedido:</h4>";
            $htmlContent .= "<pre>" . str_replace("\n", "<br>", $details['resumen_pedido']) . "</pre>";
            $email->addContent("text/html", $htmlContent);

            $sendgrid = new \SendGrid(getenv('SENDGRID_API_KEY'));
            $response = $sendgrid->send($email);

            if ($response->statusCode() >= 200 && $response->statusCode() < 300) {
                error_log("OBSERVER: SendGrid: Llamada a la API exitosa para el pedido " . $details['pedido_id']);
            } else {
                error_log("OBSERVER: SendGrid: Error en la llamada a la API. Código: " . $response->statusCode());
                error_log("OBSERVER: SendGrid: Body: " . $response->body());
            }

        } catch (Throwable $t) {
            error_log('OBSERVER: SendGrid: Excepción o Error fatal al enviar correo: '. $t->getMessage());
        }
    }
}
