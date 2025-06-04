<?php
// Configurar la zona horaria a America/Bogota
date_default_timezone_set('America/Bogota');

$errors = [];
$mostrarFormulario = true;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombreCompleto = ucwords(strtolower(trim($_POST["nombreCompleto"])));
    $tipoEmbarcacion = htmlspecialchars($_POST["tipoEmbarcacion"]);
    $destino = htmlspecialchars($_POST["destino"]);
    $numeroPersonas = htmlspecialchars($_POST["numeroPersonas"]);
    $fecha = htmlspecialchars($_POST["fecha"]);
    $whatsapp = htmlspecialchars($_POST["whatsapp"]);

    // Validación de WhatsApp
    if (!preg_match('/^\+\d{9,15}$/', $whatsapp)) {
        $errors['whatsapp'] = 'El número de WhatsApp no es válido. Debe incluir el código de país (ej. +573123456789).';
    }

    // Verificación de campos vacíos
    if (empty($nombreCompleto)) {
        $errors['nombreCompleto'] = 'El nombre completo es obligatorio.';
    }
    if (empty($tipoEmbarcacion)) {
        $errors['tipoEmbarcacion'] = 'El tipo de embarcación es obligatorio.';
    }
    if (empty($destino)) {
        $errors['destino'] = 'El destino es obligatorio.';
    }
    if (empty($numeroPersonas)) {
        $errors['numeroPersonas'] = 'El número de personas es obligatorio.';
    }
    if (empty($fecha)) {
        $errors['fecha'] = 'La fecha del paseo es obligatoria.';
    }

    // Validación del número de personas
    if (!is_numeric($numeroPersonas) || $numeroPersonas < 1) {
        $errors['numeroPersonas'] = 'El número de personas debe ser un valor válido mayor o igual a 1.';
    }

    if (!empty($errors)) {
        $mostrarFormulario = true; // Mostrar el formulario con los errores
    } else {
        // Convertir fecha a formato dd/mm/aa
        $fechaFormatted = date('d/m/y', strtotime($fecha));

        // Registrar solo la fecha actual en America/Bogota (sin hora)
        $fechaEnvio = date('d/m/Y'); // Ejemplo: "03/06/2025"

        // Lista de destinatarios con nombre y número
        $destinatarios = [
            ['nombre' => 'Kathe', 'numero' => '573245534456'],
            ['nombre' => 'Kathe', 'numero' => '573245534456'],
        ];

        // Archivo para almacenar el índice del último número usado
        $indiceFile = 'ultimo_numero.txt';

        // Leer el índice actual o inicializar en 0 si no existe
        $indiceActual = file_exists($indiceFile) ? (int)file_get_contents($indiceFile) : 0;

        // Seleccionar el destinatario actual
        $destinatario = $destinatarios[$indiceActual];
        $numeroDestino = $destinatario['numero'];
        $nombreDestino = $destinatario['nombre'];

        // Actualizar el índice para el próximo envío
        $indiceSiguiente = ($indiceActual + 1) % count($destinatarios);
        file_put_contents($indiceFile, $indiceSiguiente);

        // Formato del mensaje de WhatsApp
        $texto = urlencode("¡Hola! Quiero cotizar un paseo en bote:\n==================\n👤 Nombre: $nombreCompleto\n🚤 Tipo de Embarcación: $tipoEmbarcacion\n🏝️ Destino: $destino\n👥 Número de Personas: $numeroPersonas\n📅 Fecha: $fechaFormatted\n📱 WhatsApp: $whatsapp");

        // Enviar datos al webhook de n8n usando cURL
        $webhookUrl = "https://n8n.socialhot.co/webhook/cotizacion-bote";
        $data = [
            'nombreCompleto' => $nombreCompleto,
            'tipoEmbarcacion' => $tipoEmbarcacion,
            'destino' => $destino,
            'numeroPersonas' => $numeroPersonas,
            'fecha' => $fechaFormatted,
            'whatsapp' => $whatsapp,
            'fechaEnvio' => $fechaEnvio,
            'destinatarioNombre' => $nombreDestino,
            'destinatarioNumero' => $numeroDestino
        ];
        
        $ch = curl_init($webhookUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json", "Benko: Dc@6691400"]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        curl_close($ch);

        // Redirección directa a WhatsApp
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
    <title>Alquila un Bote en Cartagena - Islas del Rosario y Atardeceres</title>
	<link rel="icon" href="logo.svg" type="image/svg+xml">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/css/intlTelInput.min.css">
    <link href="./output.css" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; }
        .hero-bg { 
            background-image: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('panoramico.jpg'); 
            background-size: cover; 
            background-position: center; 
        }
        .section-bg { 
            background-image: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('catamaran.jpg'); 
            background-size: cover; 
            background-position: center; 
        }
        .fade-in { opacity: 0; transform: translateY(20px); transition: opacity 0.6s ease-out, transform 0.6s ease-out; }
        .fade-in.visible { opacity: 1; transform: translateY(0); }
        .floating-btn { 
            transition: transform 0.3s ease; 
            padding: 12px 16px;
            font-size: 18px;
        }
        .floating-btn:hover { 
            transform: scale(1.05); 
        }
        .iti { width: 100%; }

        /* Estilos específicos para la sección del formulario */
        .form-section {
            background-color: #f8f9fa;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 40px 20px;
            margin-top: 20px;
        }
        .form-section .header {
            text-align: center;
            margin-bottom: 0;
        }
        .form-section .logo-container {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 16px; /* Separación entre logos */
        }
        .form-section .logo {
            height: 60px; /* Altura fija para ambos logos */
            width: auto; /* Ancho se ajusta según proporciones */
            object-fit: contain; /* Mantiene proporciones */
            display: block;
        }
        @media (max-width: 600px) {
            .form-section .header {
                margin-top: 20px;
            }
            .form-section .logo {
                height: 50px; /* Altura ligeramente menor en móviles */
                width: auto;
            }
        }
        .form-section .form-container {
            background: #fff;
            padding: 24px;
            width: 100%;
            max-width: 400px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        .form-section h2 {
            text-align: center;
            color: #333;
            font-size: 2rem;
            margin-bottom: 16px;
            margin-top: 8px;
        }
        .form-section label {
            font-weight: bold;
            margin-top: 12px;
            margin-bottom: 4px;
            display: block;
            color: #444;
            font-size: 14px;
        }
        .form-section input, .form-section select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }
        .form-section input:focus, .form-section select:focus {
            border-color: #28a745;
            outline: none;
        }
        .form-section .iti {
            width: 100% !important;
        }
        .form-section button {
            width: 100%;
            padding: 12px;
            margin-top: 16px;
            background-color: #28a745;
            color: white;
            border: none;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            border-radius: 4px;
            transition: background-color 0.3s ease;
        }
        .form-section button:hover {
            background-color: #218838;
        }
        .form-section .error {
            color: #dc3545;
            font-size: 12px;
            margin-top: 6px;
        }
        /* Estilos para el badge de reseñas */
        .review-badge {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 16px;
            font-size: 16px;
            color: #333;
        }
        .badge-stars {
            color: #f5c518; /* Amarillo para las estrellas */
            font-size: 20px;
            margin-right: 8px;
        }
        /* Estilos para las estrellas de testimonios */
        .stars {
            color: #f5c518; /* Amarillo para las estrellas */
            font-size: 16px;
            margin-bottom: 8px;
        }
        /* Estilo para el texto de los testimonios */
        .testimonial-text {
            min-height: 80px;
            margin-bottom: 8px;
        }
    </style>
	<!-- Meta Pixel Code -->
<script>
!function(f,b,e,v,n,t,s)
{if(f.fbq)return;n=f.fbq=function(){n.callMethod?
n.callMethod.apply(n,arguments):n.queue.push(arguments)};
if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
n.queue=[];t=b.createElement(e);t.async=!0;
t.src=v;s=b.getElementsByTagName(e)[0];
s.parentNode.insertBefore(t,s)}(window, document,'script',
'https://connect.facebook.net/en_US/fbevents.js');
fbq('init', '1733751114203823');
fbq('track', 'PageView');
</script>
<noscript><img height="1" width="1" style="display:none"
src="https://www.facebook.com/tr?id=1733751114203823&ev=PageView&noscript=1"
/></noscript>
<!-- End Meta Pixel Code -->
</head>
<body class="bg-gray-100">
    <!-- Hero Section -->
    <section class="hero-bg h-screen flex items-center justify-center text-white">
        <div class="text-center p-8 md:p-12 rounded-lg max-w-2xl fade-in">
            <h1 class="text-4xl md:text-6xl font-bold mb-4">Vive la Magia del Caribe en un Bote Privado</h1>
            <p class="text-lg md:text-2xl mb-6">Explora las Islas del Rosario o disfruta un atardecer inolvidable en Cartagena de Indias con nuestras lanchas deportivas, catamaranes y yates.</p>
            <a href="#formulario" class="bg-green-500 hover:bg-green-600 text-white font-bold py-3 px-8 rounded-full text-lg transition duration-300 shadow-lg hover:shadow-xl">Reserva tu Bote</a>
        </div>
    </section>

    <!-- Why Choose Us Section -->
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
                    <h3 class="text-xl font-semibold mb-2">Servicio de Calidad</h3>
                    <p class="text-gray-600">Nuestro equipo profesional garantiza seguridad, comodidad y una experiencia inolvidable en el mar.</p>
                </div>
            </div>
            <a href="#formulario" class="mt-12 inline-block bg-green-500 hover:bg-green-600 text-white font-bold py-3 px-8 rounded-full text-lg transition duration-300 shadow-lg hover:shadow-xl fade-in">Cotizar Ahora</a>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="py-16 bg-gray-100">
        <div class="container mx-auto px-4 text-center">
            <!-- Badge de reseñas ficticio -->
            <div class="review-badge">
                <span class="badge-stars">★★★★★</span>
                <span>4.9 (53 reseñas)</span>
            </div>
            <h2 class="text-3xl md:text-4xl font-bold mb-12 fade-in">Lo que dicen nuestros clientes</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="bg-white p-6 rounded-lg shadow-lg fade-in">
                    <p class="text-gray-600 italic testimonial-text">"El paseo por las Islas del Rosario fue espectacular. La lancha era cómoda y el equipo súper profesional."</p>
                    <div class="stars">★★★★★</div>
                    <p class="font-semibold">María G.</p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-lg fade-in">
                    <p class="text-gray-600 italic testimonial-text">"Ver el atardecer desde un yate en Cartagena fue mágico. ¡Totalmente recomendado!"</p>
                    <div class="stars">★★★★★</div>
                    <p class="font-semibold">Juan P.</p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-lg fade-in">
                    <p class="text-gray-600 italic testimonial-text">"Una experiencia personalizada y de lujo. Volveremos pronto."</p>
                    <div class="stars">★★★★★</div>
                    <p class="font-semibold">Ana R.</p>
                </div>
            </div>
            <a href="#formulario" class="mt-12 inline-block bg-green-500 hover:bg-green-600 text-white font-bold py-3 px-8 rounded-full text-lg transition duration-300 shadow-lg hover:shadow-xl fade-in">Cotizar Ahora</a>
        </div>
    </section>

    <!-- Call to Action Section -->
    <section class="section-bg py-16 text-white">
        <div class="container mx-auto px-4 text-center p-8 md:p-12 rounded-lg max-w-2xl fade-in">
            <h2 class="text-3xl md:text-4xl font-bold mb-4">¡El Caribe te espera!</h2>
            <p class="text-lg md:text-xl mb-6">No dejes pasar la oportunidad de vivir una experiencia única en las cristalinas aguas de las Islas del Rosario o contemplando el mágico atardecer de Cartagena. Contáctanos ahora y reserva tu bote privado.</p>
            <a href="#formulario" class="bg-green-500 hover:bg-green-600 text-white font-bold py-3 px-8 rounded-full text-lg transition duration-300 shadow-lg hover:shadow-xl">Reserva tu Bote</a>
        </div>
    </section>

    <!-- Form Section -->
    <section id="formulario" class="form-section py-16">
        <div class="header">
            <div class="logo-container">
                <img src="logo-formulario-dc.svg" alt="Descubre Cartagena" class="logo">
                <img src="rl-logo.webp" alt="RL Logo" class="logo">
            </div>
        </div>
        <h2 class="font-bold">Cotizar Embarcación</h2>
        <div class="form-container">
            <?php if ($mostrarFormulario): ?>
            <form action="" method="POST" id="formulario">
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
                        <option value="Lancha de velocidad" <?php echo isset($_POST['tipoEmbarcacion']) && $_POST['tipoEmbarcacion'] == 'Lancha de velocidad' ? 'selected' : ''; ?>>Lancha de velocidad</option>
                        <option value="Catamaran" <?php echo isset($_POST['tipoEmbarcacion']) && $_POST['tipoEmbarcacion'] == 'Catamaran' ? 'selected' : ''; ?>>Catamarán</option>
                        <option value="Yate" <?php echo isset($_POST['tipoEmbarcacion']) && $_POST['tipoEmbarcacion'] == 'Yate' ? 'selected' : ''; ?>>Yate</option>
                        <option value="Velero" <?php echo isset($_POST['tipoEmbarcacion']) && $_POST['tipoEmbarcacion'] == 'Velero' ? 'selected' : ''; ?>>Velero</option>
                    </select>
                    <?php if (isset($errors['tipoEmbarcacion'])): ?>
                        <p class="error"><?php echo htmlspecialchars($errors['tipoEmbarcacion']); ?></p>
                    <?php endif; ?>
                </div>
                <div>
                    <label for="destino">Destino</label>
                    <select name="destino" id="destino" required>
                        <option value="">Seleccione un destino</option>
                        <option value="Islas del Rosario" <?php echo isset($_POST['destino']) && $_POST['destino'] == 'Islas del Rosario' ? 'selected' : ''; ?>>Islas del Rosario</option>
                        <option value="Bahia de Cartagena" <?php echo isset($_POST['destino']) && $_POST['destino'] == 'Bahia de Cartagena' ? 'selected' : ''; ?>>Bahía de Cartagena</option>
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
                <button type="submit">Cotizar Ahora por WhatsApp</button>
            </form>
            <?php endif; ?>
        </div>
    </section>

    <!-- Floating Button (Mobile Only) -->
    <a href="#formulario" id="floating-btn" class="md:hidden fixed bottom-4 left-0 right-0 w-11/12 mx-auto bg-green-500 hover:bg-green-600 text-white font-bold py-3 px-6 rounded-full text-base text-center transition duration-300 shadow-xl hover:shadow-2xl floating-btn z-50">Reserva tu Bote</a>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-8">
        <div class="container mx-auto px-4 text-center">
            <p class="mb-4">© 2025 Agencia de Tours y Alquiler de Botes en Cartagena</p>
            <p>Contáctanos por WhatsApp: <a href="https://wa.me/573205899997" target="_blank" class="underline">+57 320 589 9997</a></p>
        </div>
    </footer>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/intlTelInput.min.js"></script>
    <script>
        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({ behavior: 'smooth' });
            });
        });

        // Control floating button visibility based on form or footer (mobile only)
        const floatingBtn = document.querySelector('#floating-btn');
        const formSection = document.querySelector('#formulario');
        const footer = document.querySelector('footer');
        const observerOptions = { threshold: 0.1 };
        let isFormVisible = false;
        let isFooterVisible = false;

        const hideButtonObserver = new IntersectionObserver((entries) => {
            // Only run on mobile (width < 768px)
            if (window.innerWidth < 768) {
                entries.forEach(entry => {
                    if (entry.target === formSection) {
                        isFormVisible = entry.isIntersecting;
                    } else if (entry.target === footer) {
                        isFooterVisible = entry.isIntersecting;
                    }
                    // Hide button if form or footer is visible, show otherwise
                    floatingBtn.style.display = (isFormVisible || isFooterVisible) ? 'none' : 'block';
                });
            }
        }, observerOptions);

        if (formSection) hideButtonObserver.observe(formSection);
        if (footer) hideButtonObserver.observe(footer);

        // Update visibility on window resize
        window.addEventListener('resize', () => {
            if (window.innerWidth >= 768) {
                floatingBtn.style.display = 'none'; // Ensure hidden on desktop
            } else {
                // Restore mobile behavior
                floatingBtn.style.display = (isFormVisible || isFooterVisible) ? 'none' : 'block';
            }
        });

        // Fade-in animation on scroll
        const fadeIns = document.querySelectorAll('.fade-in');
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                }
            });
        }, observerOptions);
        fadeIns.forEach(element => observer.observe(element));

        // Inicializar intl-tel-input para el campo de WhatsApp
        document.addEventListener("DOMContentLoaded", function() {
            var input = document.querySelector("#whatsapp");
            if (input) {
                var iti = window.intlTelInput(input, {
                    preferredCountries: ["co", "br", "us", "mx", "cr", "pa"],
                    separateDialCode: true,
                    initialCountry: "co",
                    utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js"
                });
                document.querySelector("form").addEventListener("submit", function() {
                    input.value = iti.getNumber();
                });
            }
        });
    </script>
</body>
</html>