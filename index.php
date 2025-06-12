<?php
// 1. Generamos datos únicos y capturamos info del visitante en CADA carga de página
$pageViewEventId = 'pv_' . uniqid();
$clientIpAddress = $_SERVER['REMOTE_ADDR'];
$clientUserAgent = $_SERVER['HTTP_USER_AGENT'];

// Configurar la zona horaria a America/Bogota
date_default_timezone_set('America/Bogota');

$errors = [];
$mostrarFormulario = true;

// ... (El resto de tu código PHP para procesar el formulario se mantiene exactamente igual) ...
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombreCompleto = ucwords(strtolower(trim($_POST["nombreCompleto"])));
    $tipoEmbarcacion = htmlspecialchars($_POST["tipoEmbarcacion"]);
    $destino = htmlspecialchars($_POST["destino"]);
    $numeroPersonas = htmlspecialchars($_POST["numeroPersonas"]);
    $fecha = htmlspecialchars($_POST["fecha"]);
    $whatsapp = htmlspecialchars($_POST["whatsapp"]);

    if (!preg_match('/^\+\d{9,15}$/', $whatsapp)) { $errors['whatsapp'] = 'El número de WhatsApp no es válido. Debe incluir el código de país (ej. +573205899997).'; }
    if (empty($nombreCompleto)) { $errors['nombreCompleto'] = 'El nombre completo es obligatorio.'; }
    if (empty($tipoEmbarcacion)) { $errors['tipoEmbarcacion'] = 'El tipo de embarcación es obligatorio.'; }
    if (empty($destino)) { $errors['destino'] = 'El destino es obligatorio.'; }
    if (empty($numeroPersonas)) { $errors['numeroPersonas'] = 'El número de personas es obligatorio.'; }
    if (empty($fecha)) { $errors['fecha'] = 'La fecha del paseo es obligatoria.'; }
    if (!is_numeric($numeroPersonas) || $numeroPersonas < 1) { $errors['numeroPersonas'] = 'El número de personas debe ser un valor válido mayor o igual a 1.';}

    if (!empty($errors)) {
        $mostrarFormulario = true;
    } else {
        $fechaFormatted = date('d/m/y', strtotime($fecha));
        $fechaEnvio = date('d/m/Y');
        $destinatarios = [['nombre' => 'Kathe', 'numero' => '573245534652'], ['nombre' => 'Benko', 'numero' => '573245534652']];
        $indiceFile = 'ultimo_numero.txt';
        $indiceActual = file_exists($indiceFile) ? (int)file_get_contents($indiceFile) : 0;
        $destinatario = $destinatarios[$indiceActual];
        $numeroDestino = $destinatario['numero'];
        $nombreDestino = $destinatario['nombre'];
        $indiceSiguiente = ($indiceActual + 1) % count($destinatarios);
        file_put_contents($indiceFile, $indiceSiguiente);
        $texto = urlencode("¡Hola! Quiero cotizar un paseo en bote:\n==================\n👤 Nombre: $nombreCompleto\n🚤 Tipo de Embarcación: $tipoEmbarcacion\n🏝️ Destino: $destino\n👥 Número de Personas: $numeroPersonas\n📅 Fecha: $fechaFormatted\n📱 WhatsApp: $whatsapp");
        $webhookUrl = "https://n8n.socialhot.co/webhook/cotizacion-bote";
        $data = ['nombreCompleto' => $nombreCompleto, 'tipoEmbarcacion' => $tipoEmbarcacion, 'destino' => $destino, 'numeroPersonas' => $numeroPersonas, 'fecha' => $fechaFormatted, 'whatsapp' => $whatsapp, 'fechaEnvio' => $fechaEnvio, 'destinatarioNombre' => $nombreDestino, 'destinatarioNumero' => $numeroDestino];
        $ch = curl_init($webhookUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json", "Benko: Dc@6691400"]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        curl_close($ch);
        header("Location: https://api.whatsapp.com/send?phone=$numeroDestino&text=$texto");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alquiler de Botes y yates en Cartagena</title>
    <link rel="icon" href="logo.svg" type="image/svg+xml">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/css/intlTelInput.min.css">
    <link href="./output.css" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; }
        .hero-bg { background-image: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('panoramico.jpg'); background-size: cover; background-position: center; }
        .section-bg { background-image: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('catamaran.jpg'); background-size: cover; background-position: center; }
        .fade-in { opacity: 0; transform: translateY(20px); transition: opacity 0.6s ease-out, transform 0.6s ease-out; }
        .fade-in.visible { opacity: 1; transform: translateY(0); }
        .iti { width: 100%; }
        .form-section { background-color: #f8f9fa; display: flex; flex-direction: column; align-items: center; padding: 40px 20px; margin-top: 20px; }
        .form-section .header { text-align: center; margin-bottom: 16px; }
        .form-section .logo-container { display: flex; justify-content: center; align-items: center; gap: 16px; }
        .form-section .logo { height: 60px; width: auto; object-fit: contain; display: block; }
        @media (max-width: 600px) { .form-section .header { margin-top: 20px; } .form-section .logo { height: 50px; width: auto; } }
        .form-section .form-container { background: #fff; padding: 16px; width: 100%; max-width: 400px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); }
        .form-section h2 { text-align: center; color: #333; font-size: 1.5rem; margin-bottom: 16px; margin-top: 0; }
        .form-section label { font-weight: bold; margin-top: 12px; margin-bottom: 4px; display: block; color: #444; font-size: 14px; }
        .form-section input, .form-section select { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; font-size: 14px; transition: border-color 0.3s ease; }
        .form-section input:focus, .form-section select:focus { border-color: #28a745; outline: none; }
        .form-section .iti { width: 100% !important; }
        .form-section button { width: 100%; background-color: #28a745; color: white; border: none; font-size: 18px; font-weight: bold; cursor: pointer; border-radius: 4px; transition: background-color 0.3s ease; }
        .form-section button:hover { background-color: #218838; }
        .form-section .error { color: #dc3545; font-size: 12px; margin-top: 6px; }
        .review-badge { display: flex; align-items: center; justify-content: center; margin-bottom: 16px; font-size: 16px; color: #333; }
        .badge-stars { color: #f5c518; font-size: 20px; margin-right: 8px; }
        .stars { color: #f5c518; font-size: 16px; margin-bottom: 8px; }
        .testimonial-text { min-height: 80px; margin-bottom: 8px; }
        .form-section .highlight-text { font-size: 1.25rem; color: #1a7c2e; font-weight: 600; background-color: #e6f4ea; padding: 12px; border-radius: 6px; text-align: center; margin-bottom: 16px; }
        @media (min-width: 768px) { .form-section .highlight-text { max-width: 400px; } }
        .hero-bg h1 { margin-bottom: 24px; }
        .hero-bg p { margin-bottom: 32px; }
        .hero-bg a { margin-bottom: 16px; }

        /* --- ESTILOS PARA EL BOTÓN FLOTANTE --- */
        @keyframes pulse {
            0% {
                transform: scale(1);
                box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7);
            }
            70% {
                transform: scale(1);
                box-shadow: 0 0 0 15px rgba(34, 197, 94, 0);
            }
            100% {
                transform: scale(1);
                box-shadow: 0 0 0 0 rgba(34, 197, 94, 0);
            }
        }
        .floating-action-btn {
            animation: pulse 2s infinite;
            transition: opacity 0.3s ease-out, transform 0.3s ease-out;
        }
        .floating-action-btn.hidden {
            opacity: 0;
            pointer-events: none;
            transform: translateY(20px);
        }
    </style>
    <script defer>
    !function(f,b,e,v,n,t,s)
    {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
    n.callMethod.apply(n,arguments):n.queue.push(arguments);  
    if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';  
    n.queue=[];t=b.createElement(e);t.async=!0;  
    t.src=v;s=b.getElementsByTagName(e)[0];  
    s.parentNode.insertBefore(t,s)}(window, document,'script',  
    'https://connect.facebook.net/en_US/fbevents.js');  
    
    fbq('init', '1733751114203823');  
    </script>
    <noscript><img height="1" width="1" style="display:none"  
    src="https://www.facebook.com/tr?id=1733751114203823&ev=PageView&noscript=1"  
    /></noscript>
    <script async src="https://www.googletagmanager.com/gtag/js?id=AW-17157900117"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());
      gtag('config', 'AW-17157900117');
    </script>
</head>
<body class="bg-gray-100">

    <section class="hero-bg h-screen flex items-center justify-center text-white">
        <div class="text-center p-8 md:p-12 rounded-lg max-w-2xl fade-in">
            <h1 class="text-4xl md:text-6xl font-bold mb-4">Vive la Magia de Cartagena en un Bote Privado</h1>
            <p class="text-lg md:text-2xl mb-6">Explora las Islas del Rosario o disfruta un atardecer inolvidable en Cartagena de Indias con nuestras lanchas deportivas y yates.</p>
            <a href="#formulario" class="section-btn hidden md:inline-block mt-4 bg-green-500 text-white font-bold py-3 px-8 rounded-full text-lg transition duration-300 shadow-lg hover:bg-green-600 hover:shadow-xl">Cotizar Ahora</a>
        </div>
    </section>

    <section class="py-16 bg-white">
        <div class="container mx-auto px-4 text-center">
            <h2 class="text-3xl md:text-4xl font-bold mb-12 fade-in">¿Por qué elegirnos?</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="fade-in">
                    <img src="lancha.jpg" alt="Lancha Deportiva" class="mx-auto mb-4 rounded-lg shadow-md" loading="lazy">
                    <h3 class="text-xl font-semibold mb-2">Variedad de Embarcaciones</h3>
                    <p class="text-gray-600">Desde lanchas deportivas rápidas hasta catamaranes espaciosos y yates de lujo, tenemos la opción perfecta para tu aventura.</p>
                </div>
                <div class="fade-in">
                    <img src="atardecer.jpg" alt="Atardecer Privado" class="mx-auto mb-4 rounded-lg shadow-md" loading="lazy">
                    <h3 class="text-xl font-semibold mb-2">Experiencias Personalizadas</h3>
                    <p class="text-gray-600">Tú decides: un paseo privado por las Islas del Rosario o un romántico atardecer en Cartagena, adaptado a tus deseos.</p>
                </div>
                <div class="fade-in">
                    <img src="equipo.jpg" alt="Equipo Profesional" class="mx-auto mb-4 rounded-lg shadow-md" loading="lazy">
                    <h3 class="text-xl font-semibold mb-2">Navega con Expertos Locales</h3>
                    <p class="text-gray-600">Nuestra tripulación con experiencia, no solo sabe navegar, también conoce cada rincón, cada isla y cada historia de la zona.</p>
                </div>
            </div>
            <a href="#formulario" class="section-btn mt-12 inline-block bg-transparent border border-green-500 text-green-500 font-bold py-3 px-8 rounded-full text-lg transition duration-300 hover:bg-green-500 hover:text-white md:bg-green-500 md:text-white md:border-transparent md:hover:bg-green-600 fade-in">Recibir Precios y Opciones</a>
        </div>
    </section>

    <section class="py-16 bg-gray-100">
        <div class="container mx-auto px-4 text-center">
            <div class="review-badge">
                <span class="badge-stars">★★★★★</span>
                <span>4.8 / 5.0 (53 reseñas)</span>
            </div>
            <h2 class="text-3xl md:text-4xl font-bold mb-12 fade-in">Lo que dicen nuestros clientes</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="bg-white p-6 rounded-lg shadow-lg fade-in">
                    <p class="text-gray-600 italic testimonial-text">"El paseo por las Islas del Rosario fue espectacular. La lancha era cómoda y los chicos de la tripulación muy amables."</p>
                    <div class="stars">★★★★★</div>
                    <p class="font-semibold">María G.</p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-lg fade-in">
                    <p class="text-gray-600 italic testimonial-text">"Ver el atardecer de Cartagena desde un bote en la bahía es mi plan favorito. Es segunda vez que lo hago con Descubre Cartagena, los recomiendo."</p>
                    <div class="stars">★★★★★</div>
                    <p class="font-semibold">Juan P.</p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-lg fade-in">
                    <p class="text-gray-600 italic testimonial-text">"Una experiencia inolvidable con mis amigas, bailamos, nadamos y navegamos sin problemas. Volveremos pronto."</p>
                    <div class="stars">★★★★★</div>
                    <p class="font-semibold">Ana R.</p>
                </div>
            </div>
             <a href="#formulario" class="section-btn mt-12 inline-block bg-transparent border border-green-500 text-green-500 font-bold py-3 px-8 rounded-full text-lg transition duration-300 hover:bg-green-500 hover:text-white md:bg-green-500 md:text-white md:border-transparent md:hover:bg-green-600 fade-in">Cotizar por WhatsApp</a>
        </div>
    </section>

    <section class="section-bg py-16 text-white">
        <div class="container mx-auto px-4 text-center p-8 md:p-12 rounded-lg max-w-2xl fade-in">
            <h2 class="text-3xl md:text-4xl font-bold mb-4">¡Cartagena te espera!</h2>
            <p class="text-lg md:text-xl mb-6">No dejes pasar la oportunidad de vivir una experiencia única. Contáctanos ahora y reserva tu bote privado.</p>
            <a href="#formulario" class="section-btn mt-4 inline-block bg-transparent border-2 border-white text-white font-bold py-3 px-8 rounded-full text-lg transition duration-300 hover:bg-white hover:text-gray-800 md:bg-green-500 md:border-transparent md:hover:bg-green-600">Cotiza tu Bote Ya</a>
        </div>
    </section>
    
    <section id="formulario" class="form-section py-16">
        <div class="header">
            <div class="logo-container">
                <img src="logo-formulario-dc.svg" alt="Descubre Cartagena" class="logo">
                <img src="rl-logo.webp" alt="RL Logo" class="logo">
            </div>
        </div>
        <p class="highlight-text">Déjanos tus datos y te enviamos precios y opciones por WhatsApp en menos de 15 minutos.</p>
        <h2 class="font-semibold">Cotiza tu bote ya</h2>
        <div class="form-container">
            <?php if ($mostrarFormulario): ?>
            <form id="cotizacionForm" action="" method="POST">
                <div>
                    <label for="nombreCompleto">Nombre y Apellidos</label>
                    <input type="text" name="nombreCompleto" id="nombreCompleto" required value="<?php echo isset($_POST['nombreCompleto']) ? htmlspecialchars($_POST['nombreCompleto']) : ''; ?>">
                    <?php if (isset($errors['nombreCompleto'])): ?>
                        <p class="error"><?php echo htmlspecialchars($errors['nombreCompleto']); ?></p>
                    <?php endif; ?>
                </div>
                <div>
                    <label for="tipoEmbarcacion">Tipo de Embarcación</label>
                    <select name="tipoEmbarcacion" id="tipoEmbarcacion" required>
                        <option value="">Seleccione una opción</option>
                        <option value="Cualquiera" <?php echo isset($_POST['tipoEmbarcacion']) && $_POST['tipoEmbarcacion'] == 'Cualquiera' ? 'selected' : ''; ?>>Cualquiera</option>
                        <option value="Bote deportivo" <?php echo isset($_POST['tipoEmbarcacion']) && $_POST['tipoEmbarcacion'] == 'Bote deportivo' ? 'selected' : ''; ?>>Bote deportivo</option>
                        <option value="Yate" <?php echo isset($_POST['tipoEmbarcacion']) && $_POST['tipoEmbarcacion'] == 'Yate' ? 'selected' : ''; ?>>Yate</option>
                        <option value="Catamaran" <?php echo isset($_POST['tipoEmbarcacion']) && $_POST['tipoEmbarcacion'] == 'Catamaran' ? 'selected' : ''; ?>>Catamarán</option>
                    </select>
                    <?php if (isset($errors['tipoEmbarcacion'])): ?>
                        <p class="error"><?php echo htmlspecialchars($errors['tipoEmbarcacion']); ?></p>
                    <?php endif; ?>
                </div>
                <div>
                    <label for="destino">Destino</label>
                    <select name="destino" id="destino" required>
                        <option value="">Seleccione un destino</option>
                        <option value="Islas del Rosario o Cholón" <?php echo isset($_POST['destino']) && $_POST['destino'] == 'Islas del Rosario o Cholón' ? 'selected' : ''; ?>>Islas del Rosario o Cholón</option>
                        <option value="Bahía de Cartagena" <?php echo isset($_POST['destino']) && $_POST['destino'] == 'Bahía de Cartagena' ? 'selected' : ''; ?>>Bahía de Cartagena</option>
                    </select>
                    <?php if (isset($errors['destino'])): ?>
                        <p class="error"><?php echo htmlspecialchars($errors['destino']); ?></p>
                    <?php endif; ?>
                </div>
                <div>
                    <label for="numeroPersonas">Número de Personas</label>
                    <input type="number" name="numeroPersonas" id="numeroPersonas" required value="<?php echo isset($_POST['numeroPersonas']) ? htmlspecialchars($_POST['numeroPersonas']) : '1'; ?>" min="1" max="100" step="1">
                    <?php if (isset($errors['numeroPersonas'])): ?>
                        <p class="error"><?php echo htmlspecialchars($errors['numeroPersonas']); ?></p>
                    <?php endif; ?>
                </div>
                <div>
                    <label for="fecha">Fecha del Paseo</label>
                    <input type="date" name="fecha" id="fecha" required value="<?php echo isset($_POST['fecha']) ? htmlspecialchars($_POST['fecha']) : ''; ?>" min="<?php echo date('Y-m-d'); ?>">
                    <?php if (isset($errors['fecha'])): ?>
                        <p class="error"><?php echo htmlspecialchars($errors['fecha']); ?></p>
                    <?php endif; ?>
                </div>
                <div>
                    <label for="whatsapp">WhatsApp</label>
                    <input type="tel" name="whatsapp" id="whatsapp" required value="<?php echo isset($_POST['whatsapp']) ? htmlspecialchars($_POST['whatsapp']) : ''; ?>">
                    <?php if (isset($errors['whatsapp'])): ?>
                        <p class="error"><?php echo htmlspecialchars($errors['whatsapp']); ?></p>
                    <?php endif; ?>
                </div>
                <button type="submit" class="mt-4 py-3 flex items-center justify-center gap-3">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-whatsapp" viewBox="0 0 16 16">
                        <path d="M13.601 2.326A7.854 7.854 0 0 0 7.994 0C3.627 0 .068 3.558.064 7.926c0 1.399.366 2.76 1.057 3.965L0 16l4.204-1.102a7.933 7.933 0 0 0 3.79.965h.004c4.368 0 7.926-3.558 7.93-7.93A7.898 7.898 0 0 0 13.6 2.326zM7.994 14.521a6.573 6.573 0 0 1-3.356-.92l-.24-.144-2.494.654.666-2.433-.156-.251a6.56 6.56 0 0 1-1.007-3.505c0-3.626 2.957-6.584 6.591-6.584a6.56 6.56 0 0 1 4.66 1.931 6.557 6.557 0 0 1 1.928 4.66c-.004 3.639-2.961 6.592-6.592 6.592zm3.615-4.934c-.197-.099-1.17-.578-1.353-.646-.182-.065-.315-.099-.445.099-.133.197-.513.646-.627.775-.114.133-.232.148-.43.05-.197-.1-.836-.308-1.592-.985-.59-.525-.985-1.175-1.103-1.372-.114-.198-.011-.304.088-.403.087-.088.197-.232.296-.346.1-.114.133-.198.198-.33.065-.134.034-.248-.015-.347-.05-.099-.445-1.076-.612-1.47-.16-.389-.323-.335-.445-.34-.114-.007-.247-.007-.38-.007a.729.729 0 0 0-.529.247c-.182.198-.691.677-.691 1.654 0 .977.71 1.916.81 2.049.098.133 1.394 2.132 3.383 2.992.47.205.84.326 1.129.418.475.152.904.129 1.246.08.38-.058 1.171-.48 1.338-.943.164-.464.164-.86.114-.943-.049-.084-.182-.133-.38-.232z"/>
                    </svg>
                    <span>Enviar por WhatsApp</span>
                </button>
            </form>
            <?php endif; ?>
        </div>
    </section>

    <footer class="bg-gray-800 text-white py-8">
        <div class="container mx-auto px-4 text-center">
            <p class="mb-4">© 2025 Agencia de Tours y Alquiler de Botes en Cartagena</p>
            <p>Contáctanos por WhatsApp: <a href="https://wa.me/573205899997" target="_blank" class="underline">+57 320 589 9997</a></p>
        </div>
    </footer>

    <a href="#formulario" class="floating-action-btn md:hidden fixed z-50 bottom-6 left-1/2 -translate-x-1/2 inline-flex items-center justify-center gap-2 bg-green-500 text-white font-bold py-3 px-5 rounded-full shadow-lg hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-whatsapp" viewBox="0 0 16 16">
            <path d="M13.601 2.326A7.854 7.854 0 0 0 7.994 0C3.627 0 .068 3.558.064 7.926c0 1.399.366 2.76 1.057 3.965L0 16l4.204-1.102a7.933 7.933 0 0 0 3.79.965h.004c4.368 0 7.926-3.558 7.93-7.93A7.898 7.898 0 0 0 13.6 2.326zM7.994 14.521a6.573 6.573 0 0 1-3.356-.92l-.24-.144-2.494.654.666-2.433-.156-.251a6.56 6.56 0 0 1-1.007-3.505c0-3.626 2.957-6.584 6.591-6.584a6.56 6.56 0 0 1 4.66 1.931 6.557 6.557 0 0 1 1.928 4.66c-.004 3.639-2.961 6.592-6.592 6.592zm3.615-4.934c-.197-.099-1.17-.578-1.353-.646-.182-.065-.315-.099-.445.099-.133.197-.513.646-.627.775-.114.133-.232.148-.43.05-.197-.1-.836-.308-1.592-.985-.59-.525-.985-1.175-1.103-1.372-.114-.198-.011-.304.088-.403.087-.088.197-.232.296-.346.1-.114.133-.198.198-.33.065-.134.034-.248-.015-.347-.05-.099-.445-1.076-.612-1.47-.16-.389-.323-.335-.445-.34-.114-.007-.247-.007-.38-.007a.729.729 0 0 0-.529.247c-.182.198-.691.677-.691 1.654 0 .977.71 1.916.81 2.049.098.133 1.394 2.132 3.383 2.992.47.205.84.326 1.129.418.475.152.904.129 1.246.08.38-.058 1.171-.48 1.338-.943.164-.464.164-.86.114-.943-.049-.084-.182-.133-.38-.232z"/>
        </svg>
        <span>Cotizar Ahora</span>
    </a>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/intlTelInput.min.js"></script>
    <script>
        // Tus scripts de UI, con la lógica modificada para el wrapper
        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const targetId = this.getAttribute('href');
                const sectionTarget = document.querySelector(targetId);
                if (sectionTarget) {
                    const scrollTargetElement = sectionTarget.querySelector('h2.font-semibold'); 
                    if (scrollTargetElement) {
                        scrollTargetElement.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    } else {
                        sectionTarget.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                }
            });
        });

        // Apuntamos al nuevo botón
        const floatingBtn = document.querySelector('.floating-action-btn');
        const formSection = document.querySelector('#formulario');

        const updateVisibility = () => {
            if (!floatingBtn) return; // Salir si el botón no existe

            if (window.innerWidth >= 768) {
                // El botón ya se oculta en desktop con la clase md:hidden
                floatingBtn.classList.add('hidden');
                return;
            } else {
                floatingBtn.classList.remove('hidden');
            }
            
            const formRect = formSection.getBoundingClientRect();
            const viewportHeight = window.innerHeight;
            // Ocultar si el principio del formulario ya está cerca de ser visible
            const isFormVisible = formRect.top < viewportHeight - 100;

            if (isFormVisible) {
                floatingBtn.classList.add('hidden');
            } else {
                floatingBtn.classList.remove('hidden');
            }
        };
        const debouncedUpdateVisibility = debounce(updateVisibility, 100);
        window.addEventListener('scroll', debouncedUpdateVisibility);
        window.addEventListener('resize', debouncedUpdateVisibility);
        document.addEventListener('DOMContentLoaded', () => {
            // Un pequeño retraso para asegurar que todo cargue antes de mostrar el botón
            setTimeout(() => {
                updateVisibility();
            }, 100);
        });
        const fadeIns = document.querySelectorAll('.fade-in');
        const observerOptions = { threshold: 0.1, rootMargin: '0px 0px -50px 0px' };
        const fadeObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                }
            });
        }, observerOptions);
        fadeIns.forEach(element => fadeObserver.observe(element));
    </script>
    
    <script>
    // --- CÓDIGO DE SEGUIMIENTO DE EVENTOS DE FACEBOOK (VERSIÓN OPTIMIZADA) ---
    var iti; // Hacemos la instancia de intl-tel-input accesible

    document.addEventListener("DOMContentLoaded", function() {
        // Obtenemos los datos únicos generados por PHP
        const pageViewEventId = "<?php echo $pageViewEventId; ?>";
        const clientIpAddress = <?php echo json_encode($clientIpAddress); ?>;
        const clientUserAgent = <?php echo json_encode($clientUserAgent); ?>;

        var input = document.querySelector("#whatsapp");
        if (input) {
            iti = window.intlTelInput(input, {
                preferredCountries: ["co", "br", "us", "mx", "cr", "pa"],
                separateDialCode: true,
                initialCountry: "co",
                utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js"
            });
        }

        // Verificamos las variables inyectadas desde PHP
        console.log('Variables PHP:', {
            pageViewEventId: pageViewEventId,
            clientIpAddress: clientIpAddress,
            clientUserAgent: clientUserAgent
        });

        // Aseguramos que el píxel esté inicializado antes de enviar PageView con reintentos limitados
        let retryCount = 0;
        const maxRetries = 5;
        function checkPixelAndSendPageView() {
            if (typeof fbq !== 'undefined') {
                fbq('track', 'PageView', {}, { eventID: pageViewEventId });
                console.log(`Evento de Navegador 'PageView' enviado con ID: ${pageViewEventId}`);
            } else if (retryCount < maxRetries) {
                retryCount++;
                const delay = retryCount * 1000; // Incrementa el retraso (1s, 2s, 3s, etc.)
                console.warn(`Píxel de Facebook no inicializado para PageView. Reintentando en ${delay/1000} segundos... (Intento ${retryCount}/${maxRetries})`);
                setTimeout(checkPixelAndSendPageView, delay);
            } else {
                console.error('Píxel de Facebook no se inicializó después de 5 intentos. Abandonando reintentos.');
            }
        }
        checkPixelAndSendPageView();

        // Enviamos el evento del Servidor (API de Conversiones) para PageView
        function sendPageViewEvent() {
            function getCookie(name) {
                const value = `; ${document.cookie}`;
                const parts = value.split(`; ${name}=`);
                if (parts.length === 2) return parts.pop().split(';').shift();
            }

            const payload = {
                data: [{
                    event_name: "PageView",
                    event_id: pageViewEventId,
                    action_source: "website",
                    event_time: Math.floor(Date.now() / 1000),
                    event_source_url: window.location.href,
                    user_data: {
                        fbp: getCookie('_fbp') || null,
                        fbc: getCookie('_fbc') || null,
                        client_ip_address: clientIpAddress || null,
                        client_user_agent: clientUserAgent || null
                    }
                }]
            };

            console.log('Payload enviado a /event:', payload); // Depuración
            sendEventToServer(payload, 'PageView', pageViewEventId);
        }

        sendPageViewEvent();

        // --- Lógica principal para el seguimiento del formulario ---
        const cotizacionForm = document.getElementById('cotizacionForm');

        if (cotizacionForm) {
            cotizacionForm.addEventListener('submit', async function(event) {
                event.preventDefault();
                console.log('Formulario enviado. Iniciando seguimiento de evento Lead...');

                const eventoIdUnico = generateEventId();
                const telefonoCompleto = iti.getNumber();
                const nombreCompletoValue = document.getElementById('nombreCompleto').value;

                // Validamos el número de teléfono
                if (!iti.isValidNumber()) {
                    console.warn('Número de WhatsApp inválido:', telefonoCompleto);
                    document.getElementById('whatsapp').value = telefonoCompleto;
                    cotizacionForm.submit();
                    return;
                }

                // Enviamos el Evento del Navegador (Píxel)
                if (typeof fbq !== 'undefined') {
                    fbq('track', 'Lead', {}, { eventID: eventoIdUnico });
                    console.log(`Evento de Navegador 'Lead' enviado con ID: ${eventoIdUnico}`);
                } else {
                    console.warn('Píxel de Facebook no inicializado para Lead');
                }

                // Hasheamos los datos personales
                const hashedTelefono = await hashSHA256(telefonoCompleto);
                const hashedNombre = await hashSHA256(nombreCompletoValue);

                console.log(`Teléfono: ${telefonoCompleto}, Hash: ${hashedTelefono}`);
                console.log(`Nombre: ${nombreCompletoValue}, Hash: ${hashedNombre}`);

                // Enviamos el Evento del Servidor (API de Conversiones)
                const payloadCAPI = {
                    data: [{
                        event_name: "Lead",
                        event_id: eventoIdUnico,
                        action_source: "website",
                        event_time: Math.floor(Date.now() / 1000),
                        event_source_url: window.location.href,
                        user_data: {
                            ph: hashedTelefono || null,
                            fn: hashedNombre || null,
                            fbp: getCookie('_fbp') || null,
                            fbc: getCookie('_fbc') || null,
                            client_ip_address: clientIpAddress || null,
                            client_user_agent: clientUserAgent || null
                        }
                    }]
                };

                console.log('Payload enviado a /event para Lead:', payloadCAPI); // Depuración
                await sendEventToServer(payloadCAPI, 'Lead', eventoIdUnico);

                // Actualizamos el campo WhatsApp y continuamos con el envío del formulario
                document.getElementById('whatsapp').value = telefonoCompleto;
                console.log('Seguimiento completado. Reanudando envío del formulario al servidor PHP.');
                cotizacionForm.submit();
            });
        }
    });

    // --- Funciones auxiliares de seguimiento ---
    async function hashSHA256(string) {
        if (!string) return null;
        const utf8 = new TextEncoder().encode(string.trim().toLowerCase());
        const hashBuffer = await crypto.subtle.digest('SHA-256', utf8);
        const hashArray = Array.from(new Uint8Array(hashBuffer));
        return hashArray.map(b => b.toString(16).padStart(2, '0')).join('');
    }

    function generateEventId() {
        return 'event_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
    }

    function getCookie(name) {
        const value = `; ${document.cookie}`;
        const parts = value.split(`; ${name}=`);
        if (parts.length === 2) return parts.pop().split(';').shift();
    }

    // Función para enviar eventos al servidor con reintentos
    async function sendEventToServer(payload, eventName, eventId, retries = 2) {
        try {
            const response = await fetch('https://api.descubrecartagena.com/event', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const data = await response.json();
            if (response.ok) {
                console.log(`Evento de Servidor '${eventName}' enviado con éxito. ID: ${eventId}`, data);
            } else {
                throw new Error(`Respuesta no OK: ${JSON.stringify(data)}`);
            }
        } catch (error) {
            console.error(`Error al enviar evento '${eventName}' (ID: ${eventId}):`, error);
            if (retries > 0) {
                console.log(`Reintentando (${retries} intentos restantes)...`);
                await new Promise(resolve => setTimeout(resolve, 1000));
                return sendEventToServer(payload, eventName, eventId, retries - 1);
            }
            console.error(`No se pudo enviar evento '${eventName}' tras reintentos`);
        }
    }
    </script>
</body>
</html>