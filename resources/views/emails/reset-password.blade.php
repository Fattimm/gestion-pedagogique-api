<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <style>
    body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 0; }
    .container { max-width: 520px; margin: 40px auto; background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,.08); }
    .header { background: #1d4ed8; padding: 32px; text-align: center; }
    .header h1 { color: white; margin: 0; font-size: 22px; }
    .header p { color: #bfdbfe; margin: 6px 0 0; font-size: 13px; }
    .body { padding: 36px 40px; }
    .body p { color: #374151; font-size: 15px; line-height: 1.6; margin: 0 0 16px; }
    .code-box { background: #f0f9ff; border: 2px dashed #3b82f6; border-radius: 10px; text-align: center; padding: 20px; margin: 24px 0; }
    .code { font-size: 36px; font-weight: bold; letter-spacing: 10px; color: #1d4ed8; font-family: monospace; }
    .code-label { font-size: 12px; color: #6b7280; margin-top: 6px; }
    .warning { background: #fef9c3; border-left: 4px solid #facc15; padding: 12px 16px; border-radius: 6px; font-size: 13px; color: #713f12; }
    .footer { background: #f9fafb; padding: 20px 40px; text-align: center; font-size: 12px; color: #9ca3af; border-top: 1px solid #e5e7eb; }
  </style>
</head>
<body>
  <div class="container">
    <div class="header">
      <h1>Ecole 221</h1>
      <p>Plateforme de gestion pédagogique</p>
    </div>
    <div class="body">
      <p>Bonjour <strong>{{ $login }}</strong>,</p>
      <p>Vous avez demandé la réinitialisation de votre mot de passe. Voici votre code à usage unique :</p>

      <div class="code-box">
        <div class="code">{{ $code }}</div>
        <div class="code-label">Valable pendant 30 minutes</div>
      </div>

      <div class="warning">
        Si vous n'avez pas demandé cette réinitialisation, ignorez cet email. Votre mot de passe ne sera pas modifié.
      </div>
    </div>
    <div class="footer">
      © {{ date('Y') }} Ecole 221 — Cet email est automatique, ne pas répondre.
    </div>
  </div>
</body>
</html>
