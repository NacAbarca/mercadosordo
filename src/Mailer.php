<?php
declare(strict_types=1);
namespace MercadoSordo\Core;

/**
 * MercadoSordo Mailer — SMTP nativo PHP (sin Composer)
 *
 * ══════════════════════════════════════════════════════
 * OPCIÓN 1 — Servidor propio / Hosting / VPS / Enterprise
 * ══════════════════════════════════════════════════════
 * Variables Railway:
 *   SMTP_HOST      = smtp.tudominio.cl   (o mail.tudominio.cl)
 *   SMTP_PORT      = 465                 (SSL) | 587 (TLS/STARTTLS)
 *   SMTP_SECURE    = ssl                 (o tls)
 *   SMTP_USER      = noreply@mercadosordo.cl
 *   SMTP_PASS      = tu_password_smtp
 *   SMTP_FROM      = noreply@mercadosordo.cl
 *   SMTP_FROM_NAME = MercadoSordo
 *
 * ══════════════════════════════════════════════════════
 * OPCIÓN 2 (ACTIVA) — Resend.com gratis 3.000 emails/mes
 * ══════════════════════════════════════════════════════
 * Variables Railway:
 *   SMTP_HOST      = smtp.resend.com
 *   SMTP_PORT      = 587
 *   SMTP_SECURE    = tls
 *   SMTP_USER      = resend
 *   SMTP_PASS      = re_XXXXXXXXX       (tu API key de resend.com)
 *   SMTP_FROM      = noreply@mercadosordo.cl
 *   SMTP_FROM_NAME = MercadoSordo
 *
 * ══════════════════════════════════════════════════════
 * OPCIÓN 3 (FUTURA) — Gmail / Google Workspace
 * ══════════════════════════════════════════════════════
 *   SMTP_HOST      = smtp.gmail.com
 *   SMTP_PORT      = 587
 *   SMTP_SECURE    = tls
 *   SMTP_USER      = tu@gmail.com
 *   SMTP_PASS      = app_password_16chars  (App Password, no tu clave normal)
 *   SMTP_FROM      = tu@gmail.com
 *   SMTP_FROM_NAME = MercadoSordo
 *
 * ══════════════════════════════════════════════════════
 * OPCIÓN 4 (FUTURA) — SendGrid gratis 100/día
 * ══════════════════════════════════════════════════════
 *   SMTP_HOST      = smtp.sendgrid.net
 *   SMTP_PORT      = 587
 *   SMTP_SECURE    = tls
 *   SMTP_USER      = apikey
 *   SMTP_PASS      = SG.XXXXXXXXX        (tu API key de sendgrid)
 *   SMTP_FROM      = noreply@mercadosordo.cl
 *   SMTP_FROM_NAME = MercadoSordo
 */
class Mailer
{
    private string $host;
    private int    $port;
    private string $secure;
    private string $user;
    private string $pass;
    private string $from;
    private string $fromName;

    public function __construct()
    {
        $this->host     = getenv('SMTP_HOST')      ?: 'smtp.resend.com';
        $this->port     = (int)(getenv('SMTP_PORT') ?: 587);
        $this->secure   = getenv('SMTP_SECURE')    ?: 'tls';
        $this->user     = getenv('SMTP_USER')      ?: 'resend';
        $this->pass     = getenv('SMTP_PASS')      ?: '';
        $this->from     = getenv('SMTP_FROM')      ?: 'noreply@mercadosordo.cl';
        $this->fromName = getenv('SMTP_FROM_NAME') ?: 'MercadoSordo';
    }

    public static function isEnabled(): bool
    {
        return !empty(getenv('SMTP_PASS'));
    }

    /**
     * Enviar email
     */
    public function send(string $to, string $toName, string $subject, string $htmlBody, string $textBody = ''): bool
    {
        if (!self::isEnabled()) return false;

        // Capturar cualquier output accidental para no romper headers JSON
        $prevLevel = ob_get_level();
        ob_start();
        try {
            $boundary = 'ms_' . md5(uniqid());
            $headers  = $this->buildHeaders($to, $toName, $subject, $boundary);
            $body     = $this->buildBody($htmlBody, $textBody ?: strip_tags($htmlBody), $boundary);
            $result   = $this->sendViaSMTP($to, $subject, $headers, $body);
            ob_end_clean();
            return $result;
        } catch (\Throwable $e) {
            while (ob_get_level() > $prevLevel) ob_end_clean();
            error_log('[Mailer] Error: ' . $e->getMessage());
            return false;
        }
    }

    private function buildHeaders(string $to, string $toName, string $subject, string $boundary): string
    {
        $from = $this->encodeHeader($this->fromName) . ' <' . $this->from . '>';
        $toH  = $this->encodeHeader($toName) . ' <' . $to . '>';
        return implode("\r\n", [
            'From: '                       . $from,
            'To: '                         . $toH,
            'Subject: '                    . $this->encodeHeader($subject),
            'MIME-Version: 1.0',
            'Content-Type: multipart/alternative; boundary="' . $boundary . '"',
            'X-Mailer: MercadoSordo-PHP/1.0',
            'Date: '                       . date('r'),
        ]);
    }

    private function buildBody(string $html, string $text, string $boundary): string
    {
        return "--{$boundary}\r\n"
            . "Content-Type: text/plain; charset=UTF-8\r\n"
            . "Content-Transfer-Encoding: base64\r\n\r\n"
            . chunk_split(base64_encode($text)) . "\r\n"
            . "--{$boundary}\r\n"
            . "Content-Type: text/html; charset=UTF-8\r\n"
            . "Content-Transfer-Encoding: base64\r\n\r\n"
            . chunk_split(base64_encode($html)) . "\r\n"
            . "--{$boundary}--";
    }

    private function sendViaSMTP(string $to, string $subject, string $headers, string $body): bool
    {
        $context = stream_context_create([
            'ssl' => [
                'verify_peer'       => false,
                'verify_peer_name'  => false,
                'allow_self_signed' => true,
            ],
        ]);

        // SSL directo (puerto 465) o STARTTLS (587/25) — timeout 5s máximo
        if ($this->secure === 'ssl') {
            $socket = stream_socket_client(
                "ssl://{$this->host}:{$this->port}", $errno, $errstr, 5,
                STREAM_CLIENT_CONNECT, $context
            );
        } else {
            $socket = stream_socket_client(
                "tcp://{$this->host}:{$this->port}", $errno, $errstr, 5
            );
        }

        if (!$socket) throw new \RuntimeException("SMTP connect failed: {$errstr} ({$errno})");
        stream_set_timeout($socket, 5);

        $this->expect($socket, 220);
        $this->cmd($socket, "EHLO " . gethostname());
        $resp = $this->readAll($socket);

        // STARTTLS si corresponde
        if ($this->secure === 'tls' && str_contains($resp, 'STARTTLS')) {
            $this->cmd($socket, 'STARTTLS');
            $this->expect($socket, 220);
            stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
            $this->cmd($socket, "EHLO " . gethostname());
            $this->readAll($socket);
        }

        // Auth LOGIN
        $this->cmd($socket, 'AUTH LOGIN');
        $this->expect($socket, 334);
        $this->cmd($socket, base64_encode($this->user));
        $this->expect($socket, 334);
        $this->cmd($socket, base64_encode($this->pass));
        $this->expect($socket, 235);

        // Envelope
        $this->cmd($socket, "MAIL FROM:<{$this->from}>");
        $this->expect($socket, 250);
        $this->cmd($socket, "RCPT TO:<{$to}>");
        $this->expect($socket, 250);
        $this->cmd($socket, 'DATA');
        $this->expect($socket, 354);

        // Mensaje
        fwrite($socket, $headers . "\r\n\r\n" . $body . "\r\n.\r\n");
        $this->expect($socket, 250);
        $this->cmd($socket, 'QUIT');
        fclose($socket);
        return true;
    }

    private function cmd($socket, string $cmd): void
    {
        fwrite($socket, $cmd . "\r\n");
    }

    private function expect($socket, int $code): string
    {
        $resp = '';
        while ($line = fgets($socket, 512)) {
            $resp .= $line;
            if (substr($line, 3, 1) === ' ') break;
        }
        $actual = (int)substr($resp, 0, 3);
        if ($actual !== $code) {
            throw new \RuntimeException("SMTP expected {$code}, got {$actual}: {$resp}");
        }
        return $resp;
    }

    private function readAll($socket): string
    {
        $resp = '';
        while ($line = fgets($socket, 512)) {
            $resp .= $line;
            if (substr($line, 3, 1) === ' ') break;
        }
        return $resp;
    }

    private function encodeHeader(string $str): string
    {
        if (preg_match('/[^\x20-\x7E]/', $str)) {
            return '=?UTF-8?B?' . base64_encode($str) . '?=';
        }
        return $str;
    }

    // ══════════════════════════════════════════════════════
    // TEMPLATES DE EMAIL
    // ══════════════════════════════════════════════════════

    public static function templateBase(string $title, string $body, string $cta = '', string $ctaUrl = ''): string
    {
        $ctaBtn = $cta ? "
            <div style='text-align:center;margin:28px 0'>
              <a href='{$ctaUrl}' style='background:#1B4F8A;color:white;padding:14px 32px;border-radius:8px;text-decoration:none;font-weight:700;font-size:1rem;display:inline-block'>{$cta}</a>
            </div>" : '';

        return <<<HTML
        <!DOCTYPE html>
        <html lang="es">
        <head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head>
        <body style="margin:0;padding:0;background:#EBF2FB;font-family:Helvetica Neue,Helvetica,Arial,sans-serif">
          <table width="100%" cellpadding="0" cellspacing="0">
            <tr><td align="center" style="padding:32px 16px">
              <table width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background:white;border-radius:12px;overflow:hidden;box-shadow:0 4px 16px rgba(0,0,0,.08)">
                <!-- Header -->
                <tr><td style="background:#1B4F8A;padding:24px 32px;text-align:center">
                  <div style="font-family:Sora,Arial,sans-serif;font-size:1.6rem;font-weight:800;color:white">
                    Mercado<span style="color:#F4C430">Sordo</span>
                  </div>
                </td></tr>
                <!-- Body -->
                <tr><td style="padding:32px">
                  <h2 style="color:#0A1628;margin:0 0 16px;font-size:1.25rem">{$title}</h2>
                  {$body}
                  {$ctaBtn}
                </td></tr>
                <!-- Footer -->
                <tr><td style="background:#f8f9fa;padding:20px 32px;border-top:1px solid #e9ecef;text-align:center">
                  <p style="color:#6c757d;font-size:.8rem;margin:0">
                    © 2026 MercadoSordo · Developed by Nac Abarca<br>
                    <a href="https://mercadosordo-production.up.railway.app" style="color:#1B4F8A">mercadosordo-production.up.railway.app</a>
                  </p>
                </td></tr>
              </table>
            </td></tr>
          </table>
        </body>
        </html>
        HTML;
    }

    /** 1. Pedido creado → comprador */
    public static function orderCreated(array $order, array $items): string
    {
        $rows = '';
        foreach ($items as $it) {
            $rows .= "<tr>
              <td style='padding:8px 0;border-bottom:1px solid #f0f0f0'>{$it['title']}</td>
              <td style='padding:8px 0;border-bottom:1px solid #f0f0f0;text-align:center'>{$it['quantity']}</td>
              <td style='padding:8px 0;border-bottom:1px solid #f0f0f0;text-align:right;color:#1B4F8A;font-weight:700'>$" . number_format($it['subtotal'],0,',','.') . "</td>
            </tr>";
        }

        $body = "
          <p style='color:#444;line-height:1.6'>Hola, tu pedido fue creado exitosamente. El vendedor confirmará el pago en breve.</p>
          <div style='background:#f8f9fa;border-radius:8px;padding:16px;margin:16px 0'>
            <strong>N° Orden:</strong> {$order['order_number']}<br>
            <strong>Método de pago:</strong> " . ucfirst($order['payment_method']) . "<br>
            <strong>Estado:</strong> Pendiente de confirmación
          </div>
          <table width='100%' cellpadding='0' cellspacing='0' style='font-size:.92rem'>
            <thead><tr>
              <th style='text-align:left;padding-bottom:8px;color:#666;font-weight:600'>Producto</th>
              <th style='text-align:center;padding-bottom:8px;color:#666;font-weight:600'>Cant.</th>
              <th style='text-align:right;padding-bottom:8px;color:#666;font-weight:600'>Subtotal</th>
            </tr></thead>
            <tbody>{$rows}</tbody>
            <tfoot><tr>
              <td colspan='2' style='padding-top:12px;font-weight:700'>Total</td>
              <td style='padding-top:12px;font-weight:800;color:#1B4F8A;text-align:right;font-size:1.1rem'>$" . number_format($order['total'],0,',','.') . "</td>
            </tr></tfoot>
          </table>";

        $appUrl = getenv('APP_URL') ?: 'https://mercadosordo-production.up.railway.app';
        return self::templateBase('✅ Pedido creado — ' . $order['order_number'], $body, 'Ver mi pedido', $appUrl . '/#orders');
    }

    /** 2. Pago recibido → vendedor */
    public static function paymentReceived(array $order, array $financials): string
    {
        $appUrl = getenv('APP_URL') ?: 'https://mercadosordo-production.up.railway.app';
        $neto   = number_format($financials['vendor_net'], 0, ',', '.');
        $comm   = number_format($financials['commission_amount'], 0, ',', '.');
        $total  = number_format($order['total'], 0, ',', '.');

        $body = "
          <p style='color:#444;line-height:1.6'>Tienes un nuevo pedido pagado. Acéptalo para comenzar el proceso de despacho.</p>
          <div style='background:#d4edda;border-radius:8px;padding:16px;margin:16px 0;border-left:4px solid #28a745'>
            <strong>N° Orden:</strong> {$order['order_number']}<br>
            <strong>Total recibido:</strong> \${$total} CLP<br>
            <strong>Comisión plataforma (5%):</strong> -\${$comm} CLP<br>
            <strong style='color:#1B4F8A;font-size:1.05rem'>💰 Recibes: \${$neto} CLP</strong>
          </div>
          <p style='color:#666;font-size:.88rem'>Tienes 24 horas para aceptar el pedido. Si no lo aceptas, se cancelará automáticamente.</p>";

        return self::templateBase('💰 Pago recibido — ' . $order['order_number'], $body, 'Aceptar pedido', $appUrl . '/#vendor-orders');
    }

    /** 3. Orden aceptada → comprador */
    public static function orderAccepted(array $order): string
    {
        $appUrl = getenv('APP_URL') ?: 'https://mercadosordo-production.up.railway.app';
        $body = "
          <p style='color:#444;line-height:1.6'>¡Buenas noticias! El vendedor aceptó tu pedido y está preparando el envío.</p>
          <div style='background:#cce5ff;border-radius:8px;padding:16px;margin:16px 0;border-left:4px solid #1B4F8A'>
            <strong>N° Orden:</strong> {$order['order_number']}<br>
            <strong>Estado:</strong> En proceso — preparando envío
          </div>
          <p style='color:#666;font-size:.88rem'>Te notificaremos cuando tu pedido sea despachado con el número de seguimiento.</p>";

        return self::templateBase('📦 Tu pedido fue aceptado', $body, 'Ver mi compra', $appUrl . '/#orders');
    }

    /** 4. Orden despachada → comprador */
    public static function orderDispatched(array $order): string
    {
        $appUrl  = getenv('APP_URL') ?: 'https://mercadosordo-production.up.railway.app';
        $carrier = $order['tracking_carrier'] ?? 'el transportista';
        $tracking = $order['tracking_number'] ?? '';
        $trackingInfo = $tracking
            ? "<strong>N° Seguimiento:</strong> {$tracking}<br><strong>Carrier:</strong> {$carrier}"
            : "<strong>Carrier:</strong> {$carrier}";

        $body = "
          <p style='color:#444;line-height:1.6'>Tu pedido fue despachado y está en camino.</p>
          <div style='background:#fff3cd;border-radius:8px;padding:16px;margin:16px 0;border-left:4px solid #F4C430'>
            <strong>N° Orden:</strong> {$order['order_number']}<br>
            {$trackingInfo}
          </div>
          <p style='color:#444;line-height:1.6'>Una vez que recibas tu pedido, confírmalo en la plataforma para liberar los fondos al vendedor.</p>
          <p style='color:#666;font-size:.88rem'>Si no confirmas en 7 días, la orden se completará automáticamente.</p>";

        return self::templateBase('🚚 Tu pedido está en camino', $body, 'Confirmar recepción', $appUrl . '/#orders');
    }

    /** 5. Orden completada → vendedor */
    public static function orderCompleted(array $order, array $financials): string
    {
        $appUrl = getenv('APP_URL') ?: 'https://mercadosordo-production.up.railway.app';
        $neto   = number_format($financials['vendor_net'], 0, ',', '.');

        $body = "
          <p style='color:#444;line-height:1.6'>El comprador confirmó la recepción de su pedido. Los fondos han sido liberados.</p>
          <div style='background:#d4edda;border-radius:8px;padding:16px;margin:16px 0;border-left:4px solid #28a745'>
            <strong>N° Orden:</strong> {$order['order_number']}<br>
            <strong style='color:#1B4F8A;font-size:1.1rem'>✅ Fondos liberados: \${$neto} CLP</strong>
          </div>";

        return self::templateBase('🎉 Venta completada — Fondos liberados', $body, 'Ver mis ventas', $appUrl . '/#vendor-orders');
    }

    /** 6. Nuevo mensaje en chat → destinatario */
    public static function newChatMessage(array $order, string $senderName, string $message): string
    {
        $appUrl  = getenv('APP_URL') ?: 'https://mercadosordo-production.up.railway.app';
        $preview = mb_substr(strip_tags($message), 0, 120);

        $body = "
          <p style='color:#444;line-height:1.6'><strong>{$senderName}</strong> te envió un mensaje sobre el pedido <strong>{$order['order_number']}</strong>:</p>
          <div style='background:#f8f9fa;border-radius:8px;padding:16px;margin:16px 0;border-left:4px solid #1B4F8A;font-style:italic;color:#444'>
            \"{$preview}\"
          </div>";

        return self::templateBase('💬 Nuevo mensaje de ' . $senderName, $body, 'Responder', $appUrl . '/#vendor-orders');
    }

    /** 7. Reporte diario Admin — Audit Log */
    public static function adminDailyReport(array $stats, array $recentOrders): string
    {
        $appUrl = getenv('APP_URL') ?: 'https://mercadosordo-production.up.railway.app';
        $date   = date('d/m/Y');

        $ordersRows = '';
        foreach ($recentOrders as $o) {
            $ordersRows .= "<tr>
              <td style='padding:6px 8px;font-size:.85rem'>{$o['order_number']}</td>
              <td style='padding:6px 8px;font-size:.85rem'>{$o['buyer_name']}</td>
              <td style='padding:6px 8px;font-size:.85rem'>\$" . number_format($o['total'],0,',','.') . "</td>
              <td style='padding:6px 8px;font-size:.85rem'>{$o['status']}</td>
            </tr>";
        }

        $body = "
          <p style='color:#444'>Resumen del día <strong>{$date}</strong>:</p>
          <table width='100%' style='border-collapse:collapse;margin:16px 0'>
            <tr style='background:#1B4F8A;color:white'>
              <td style='padding:10px;font-weight:700;border-radius:8px 0 0 0'>Órdenes hoy</td>
              <td style='padding:10px;font-weight:700'>Ingresos hoy</td>
              <td style='padding:10px;font-weight:700'>Comisión</td>
              <td style='padding:10px;font-weight:700;border-radius:0 8px 0 0'>Usuarios nuevos</td>
            </tr>
            <tr style='background:#f8f9fa;text-align:center'>
              <td style='padding:12px;font-size:1.4rem;font-weight:800;color:#1B4F8A'>" . ($stats['orders_today'] ?? 0) . "</td>
              <td style='padding:12px;font-size:1.1rem;font-weight:700'>\$" . number_format($stats['revenue_today'] ?? 0, 0, ',', '.') . "</td>
              <td style='padding:12px;font-size:1.1rem;font-weight:700;color:#28a745'>\$" . number_format($stats['commission_today'] ?? 0, 0, ',', '.') . "</td>
              <td style='padding:12px;font-size:1.1rem;font-weight:700'>" . ($stats['new_users'] ?? 0) . "</td>
            </tr>
          </table>
          <h4 style='color:#0A1628;font-size:.95rem;margin:20px 0 8px'>Órdenes recientes</h4>
          <table width='100%' style='border-collapse:collapse;font-size:.85rem'>
            <thead><tr style='border-bottom:2px solid #dee2e6'>
              <th style='padding:6px 8px;text-align:left;color:#666'>N° Orden</th>
              <th style='padding:6px 8px;text-align:left;color:#666'>Comprador</th>
              <th style='padding:6px 8px;text-align:left;color:#666'>Total</th>
              <th style='padding:6px 8px;text-align:left;color:#666'>Estado</th>
            </tr></thead>
            <tbody>{$ordersRows}</tbody>
          </table>";

        return self::templateBase("📊 Reporte diario MercadoSordo — {$date}", $body, 'Ver Dashboard', $appUrl . '/admin');
    }
}
