<?php
/**
 * Bliv Sponsor (Become Sponsor) Page
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/security.php'; 

$page_title = 'Bliv Sponsor';

?>

<?php require_once __DIR__ . '/includes/header.php'; ?>

<section class="section">
    <h2 class="section-title">Bliv Sponsor</h2>

    <p class="section-subtitle">
        Vil din virksomhed være med til at støtte Bogø Hallen og lokal sport?
    </p>

    <div class="two-column">
        <div class="two-column-content">
            <h3 style="font-size: 18px; margin: 20px 0 15px; border-bottom: 2px solid #ecf0f1; padding-bottom: 10px;">Hvorfor blive sponsor?</h3>

            <p>
                Som sponsor af Bogø Hallen får din virksomhed:
            </p>

            <ul style="margin-left: 20px; margin-bottom: 20px;">
                <li><strong>Synlighed:</strong> Dit logo vises på vores hjemmeside, sociale medier og på centeret</li>
                <li><strong>Goodwill:</strong> Styrk dit brand gennem engagement i lokal sport og fællesskab</li>
                <li><strong>Netværk:</strong> Kontakt med andre virksomheder og lokale ledere</li>
                <li><strong>Skattefradrag:</strong> Sponsorbidrag kan være fradragsberettiget (kontakt revisor)</li>
                <li><strong>Særlige arrangementer:</strong> Mulighed for at være sponsor på specielle events og turneringer</li>
            </ul>

            <h3 style="font-size: 18px; margin: 30px 0 15px; border-bottom: 2px solid #ecf0f1; padding-bottom: 10px;">Sponsorniveauer</h3>

            <p>
                Vi tilbyder tre sponsorniveauer, som kan tilpasses dit budget:
            </p>

            <ul style="margin-left: 20px; margin-bottom: 20px;">
                <li><strong>Guldspons:</strong> 50.000+ kr. pr. år</li>
                <li><strong>Sølvspons:</strong> 25.000-49.999 kr. pr. år</li>
                <li><strong>Bronzespons:</strong> 10.000-24.999 kr. pr. år</li>
            </ul>

            <p>
                Hver sponsorpakke kan tilpasses dine behov. <a href="/kontakt.php">Kontakt os</a> for at diskutere muligheder.
            </p>
        </div>

        <div>
            <div style="background: #f9f9f9; padding: 30px; border-radius: 4px;">
                <h3 style="font-size: 18px; margin-bottom: 20px;">Kontakt os</h3>

                <p style="margin-bottom: 15px;">
                    Interesseret i at blive sponsor? Vi glæder os til at høre fra dig!
                </p>

                <div style="margin-bottom: 20px; padding: 15px; background: white; border-radius: 4px;">
                    <h4 style="margin-bottom: 8px; font-size: 13px; font-weight: 600; text-transform: uppercase;">Email</h4>
                    <p>
                        <a href="mailto:<?php echo safe_html(get_content('footer', 'contact_email', 'kontakt@bogohallen.dk')); ?>">
                            <?php echo safe_html(get_content('footer', 'contact_email', 'kontakt@bogohallen.dk')); ?>
                        </a>
                    </p>
                </div>

                <div style="margin-bottom: 20px; padding: 15px; background: white; border-radius: 4px;">
                    <h4 style="margin-bottom: 8px; font-size: 13px; font-weight: 600; text-transform: uppercase;">Adresse</h4>
                    <p><?php echo safe_html(get_content('footer', 'address', 'Bogø Idrætscenter, Bogø Idrætspark 1, 4773 Kalvebod')); ?></p>
                </div>

                <div style="padding: 15px; background: white; border-radius: 4px;">
                    <h4 style="margin-bottom: 8px; font-size: 13px; font-weight: 600; text-transform: uppercase;">CVR</h4>
                    <p><?php echo safe_html(get_content('footer', 'cvr', 'CVR: 12345678')); ?></p>
                </div>

                <p style="font-size: 13px; color: #7f8c8d; margin-top: 20px;">
                    Vi kontakter dig inden for 2 arbejdsdage.
                </p>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
