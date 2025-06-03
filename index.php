<?php
// Configurar la zona horaria a America/Bogotá
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

        // Registrar solo la fecha actual en America/Bogotá (sin hora)
        $fechaEnvio = date('d/m/Y'); // Ejemplo: "01/06/2025"

        // Lista de destinatarios con nombre y número
        $destinatarios = [
            ['nombre' => 'Kathe', 'numero' => '573245534652'],
            ['nombre' => 'Hannia', 'numero' => '573166146661'],
        ];

        // Archivo para almacenar el índice del último destinatario usado
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
        $texto = urlencode("¡Hola! Quiero cotizar un paseo en bote\n==================\n👤 Nombre: $nombreCompleto\n🚤 Tipo de Embarcación: $tipoEmbarcacion\n🏝️ Destino: $destino\n👥 Número de Personas: $numeroPersonas\n📅 Fecha: $fechaFormatted\n📱 WhatsApp: $whatsapp");

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
    <!-- Pixel de Facebook (mantener tu ID) -->
    <!-- Inserta el código del Pixel de Facebook aquí. Obtén el código desde el Administrador de Eventos de Facebook y pégalo justo después de esta línea. -->
    <title>Alquila un Bote en Cartagena - Islas del Rosario y Atardeceres</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/css/intlTelInput.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
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
            padding: 12px 20px; /* Aumentado para mejor usabilidad en móvil */
            font-size: 18px; /* Tamaño de fuente más grande */
        }
        .floating-btn:hover { 
            transform: scale(1.05); 
        }
        .iti { width: 100%; }

        /* Estilos específicos para la sección del formulario */
        .form-section * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Poppins', sans-serif;
        }
        .form-section {
            background-color: #f8f9fa;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px;
            margin-top: 20px;
        }
        .form-section .header {
            text-align: center;
            margin-bottom: 0px;
        }
        .form-section .header img {
            width: 70%;
            max-width: 300px;
            display: block;
            margin: 0 auto;
        }
        @media (max-width: 768px) {
            .form-section .header {
                margin-top: 20px;
            }
        }
        .form-section .container {
            background: #fff;
            padding: 20px;
            width: 100%;
            max-width: 400px;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        }
        .form-section h2 {
            text-align: center;
            color: #333;
            font-size: 2rem;
            margin-bottom: 20px;
            margin-top: 10px;
        }
        .form-section label {
            font-weight: 600;
            margin-top: 10px;
            margin-bottom: 3px;
            display: block;
            color: #555;
            font-size: 14px;
        }
        .form-section input, .form-section select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
        }
        .form-section .iti {
            width: 100% !important;
        }
        .form-section button {
            width: 100%;
            padding: 10px;
            margin-top: 15px;
            background: #28a745; /* Verde más vibrante */
            color: white;
            border: none;
            font-size: 16px;
            cursor: pointer;
            border-radius: 5px;
        }
        .form-section button:hover {
            background: #218838; /* Hover más contrastado */
        }
        .form-section .error {
            color: #dc3545;
            font-size: 12px;
            margin-top: 3px;
        }
    </style>
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
            <h2 class="text-3xl md:text-4xl font-bold mb-12 fade-in">Lo que dicen nuestros clientes</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="bg-white p-6 rounded-lg shadow-lg fade-in">
                    <p class="text-gray-600 italic mb-4">"El paseo por las Islas del Rosario fue espectacular. La lancha era cómoda y el equipo súper profesional."</p>
                    <p class="font-semibold">María G.</p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-lg fade-in">
                    <p class="text-gray-600 italic mb-4">"Ver el atardecer desde un yate en Cartagena fue mágico. ¡Totalmente recomendado!"</p>
                    <p class="font-semibold">Juan P.</p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-lg fade-in">
                    <p class="text-gray-600 italic mb-4">"Una experiencia personalizada y de lujo. Volveremos pronto."</p>
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
            <img src="Logo-formulario-dc.svg" alt="Descubre Cartagena">
        </div>
        <h2>Cotizar Embarcación</h2>
        <div class="container">
            <?php if ($mostrarFormulario): ?>
            <form action="" method="POST" id="formulario">
                <div>
                    <label for="nombreCompleto">Nombre y Apellidos</label>
                    <input type="text" name="nombreCompleto" id="nombreCompleto" required value="<?php echo isset($_POST['nombreCompleto']) ? htmlspecialchars($_POST['nombreCompleto']) : ''; ?>">
                    <?php if (isset($errors['nombreCompleto'])): ?>
                        <p class="error"><?php echo $errors['nombreCompleto']; ?></p>
                    <?php endif; ?>
                </div>
                <div>
                    <label for="tipoEmbarcacion">Tipo de Embarcación</label>
                    <select name="tipoEmbarcacion" id="tipoEmbarcacion" required>
                        <option value="">Seleccione una opción</option>
                        <option value="Cualquiera" <?php echo isset($_POST['tipoEmbarcacion']) && $_POST['tipoEmbarcacion'] == 'Cualquiera' ? 'selected' : ''; ?>>Cualquiera</option>
                        <option value="Bote deportivo" <?php echo isset($_POST['tipoEmbarcacion']) && $_POST['tipoEmbarcacion'] == 'Bote deportivo' ? 'selected' : ''; ?>>Bote Deportivo</option>
                        <option value="Catamarán" <?php echo isset($_POST['tipoEmbarcacion']) && $_POST['tipoEmbarcacion'] == 'Catamarán' ? 'selected' : ''; ?>>Catamarán</option>
                        <option value="Yate" <?php echo isset($_POST['tipoEmbarcacion']) && $_POST['tipoEmbarcacion'] == 'Yate' ? 'selected' : ''; ?>>Yate</option>
                        <option value="Velero" <?php echo isset($_POST['tipoEmbarcacion']) && $_POST['tipoEmbarcacion'] == 'Velero' ? 'selected' : ''; ?>>Velero</option>
                    </select>
                    <?php if (isset($errors['tipoEmbarcacion'])): ?>
                        <p class="error"><?php echo $errors['tipoEmbarcacion']; ?></p>
                    <?php endif; ?>
                </div>
                <div>
                    <label for="destino">Destino</label>
                    <select name="destino" id="destino" required>
                        <option value="">Seleccione un destino</option>
                        <option value="Islas del Rosario" <?php echo isset($_POST['destino']) && $_POST['destino'] == 'Islas del Rosario' ? 'selected' : ''; ?>>Islas del Rosario</option>
                        <option value="Bahía de Cartagena" <?php echo isset($_POST['destino']) && $_POST['destino'] == 'Bahía de Cartagena' ? 'selected' : ''; ?>>Bahía de Cartagena</option>
                    </select>
                    <?php if (isset($errors['destino'])): ?>
                        <p class="error"><?php echo $errors['destino']; ?></p>
                    <?php endif; ?>
                </div>
                <div>
                    <label for="numeroPersonas">Número de Personas</label>
                    <input type="number" name="numeroPersonas" id="numeroPersonas" required value="<?php echo isset($_POST['numeroPersonas']) ? htmlspecialchars($_POST['numeroPersonas']) : '1'; ?>" min="1" max="100" step="1">
                    <?php if (isset($errors['numeroPersonas'])): ?>
                        <p class="error"><?php echo $errors['numeroPersonas']; ?></p>
                    <?php endif; ?>
                </div>
                <div>
                    <label for="fecha">Fecha del Paseo</label>
                    <input type="date" name="fecha" id="fecha" required value="<?php echo isset($_POST['fecha']) ? htmlspecialchars($_POST['fecha']) : ''; ?>" min="<?php echo date('Y-m-d'); ?>">
                    <?php if (isset($errors['fecha'])): ?>
                        <p class="error"><?php echo $errors['fecha']; ?></p>
                    <?php endif; ?>
                </div>
                <div>
                    <label for="whatsapp">WhatsApp</label>
                    <input type="tel" name="whatsapp" id="whatsapp" required value="<?php echo isset($_POST['whatsapp']) ? htmlspecialchars($_POST['whatsapp']) : ''; ?>">
                    <?php if (isset($errors['whatsapp'])): ?>
                        <p class="error"><?php echo $errors['whatsapp']; ?></p>
                    <?php endif; ?>
                </div>
                <button type="submit">Cotizar Ahora por WhatsApp</button>
            </form>
            <?php endif; ?>
        </div>
    </section>

    <!-- Floating Button (Mobile Only) -->
    <a href="#formulario" class="md:hidden fixed bottom-4 left-0 right-0 w-11/12 mx-auto bg-green-500 hover:bg-green-600 text-white font-bold py-3 px-6 rounded-full text-base text-center transition duration-300 shadow-xl hover:shadow-2xl floating-btn z-50">Reserva tu Bote</a>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-8">
        <div class="container mx-auto px-4 text-center">
            <p class="mb-4">© 2025 Agencia de Tours y Alquiler de Botes en Cartagena</p>
            <p>Contáctanos: <a href="https://wa.me/573123456789" target="_blank" class="underline">+57 312 345 6789</a></p>
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

        // Fade-in animation on scroll
        const fadeIns = document.querySelectorAll('.fade-in');
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                }
            });
        }, { threshold: 0.1 });
        fadeIns.forEach(element => observer.observe(element));

        // Inicializar intl-tel-input para el campo de WhatsApp
        document.addEventListener("DOMContentLoaded", function() {
            var input = document.querySelector("#whatsapp");
            var iti = window.intlTelInput(input, {
                preferredCountries: ["co", "br", "us", "mx", "cr", "pa"],
                separateDialCode: true,
                initialCountry: "co",
                utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js"
            });
            document.querySelector("form").addEventListener("submit", function() {
                input.value = iti.getNumber();
            });
        });
    </script>
</body>
</html>