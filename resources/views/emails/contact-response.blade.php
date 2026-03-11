<!DOCTYPE html>
<html lang="it">
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0;">
    <div style="background-color: #000; padding: 30px; text-align: center;">
        <h1 style="color: #ffc107; margin: 0; text-transform: uppercase;">FitLife Milano</h1>
    </div>
    <div style="padding: 30px; border: 1px solid #eee; max-width: 600px; margin: 20px auto;">
        <p>Ciao <strong>{{ $first_name }}</strong>,</p>
        <p>Ti ringraziamo per averci contattato. In merito alla tua richiesta riguardo a <strong>"{{ $subject }}"</strong>, ecco la nostra risposta:</p>
        
        <div style="background: #fdfdfd; padding: 20px; border-left: 5px solid #ffc107; margin: 25px 0; font-style: italic; color: #555; border-radius: 4px; box-shadow: inset 0 0 10px rgba(0,0,0,0.05);">
            {!! nl2br(e($replyText)) !!}
        </div>
        
        <p>Speriamo di vederti presto in palestra!</p>
        <p style="margin-top: 30px;">Cordiali saluti,<br><strong>Lo staff di FitLife Milano</strong></p>
    </div>
    <div style="text-align: center; padding: 20px; font-size: 12px; color: #999;">
        © 2026 FitLife Milano. Tutti i diritti riservati.
    </div>
</body>
</html>