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
        $fechaEnvio = date('d/m/Y'); // Ejemplo: "25/03/2025"

        // Formato del mensaje de WhatsApp
        $numeroDestino = "573166146661";
        $texto = urlencode("¡Hola! Quiero cotizar un paseo en bote\n==================\n👤 Nombre: $nombreCompleto\n🚤 Tipo de Embarcación: $tipoEmbarcacion\n🏝️ Destino: $destino\n👥 Número de Personas: $numeroPersonas\n📅 Fecha: $fechaFormatted\n📱 WhatsApp: $whatsapp");

        // Enviar datos al webhook de n8n usando cURL
        $webhookUrl = "https://ia.socialhot.co/webhook/cotizacion-bote";
        $data = [
            'nombreCompleto' => $nombreCompleto,
            'tipoEmbarcacion' => $tipoEmbarcacion,
            'destino' => $destino,
            'numeroPersonas' => $numeroPersonas,
            'fecha' => $fechaFormatted,
            'whatsapp' => $whatsapp,
            'fechaEnvio' => $fechaEnvio
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
        }
        .floating-btn:hover { 
            transform: scale(1.05); 
        }
        .iti { width: 100%; }
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
                    <p class="text-gray-600 italic mb-4">"Ver el atardecer desde un yate en Cartagena оке mágico. ¡Totalmente recomendado!"</p>
                    <p class="font-semibold">Juan P.</p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-lg fade-in">
                    <p class="text-gray-600 italic mb-4">"Una experiencia personalizada y de lujo. Volveremos pronto."</p>
                    <p class="font-semibold">Ana R.</p>
                </div>
            </div>
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
    <section id="formulario" class="py-16 bg-white">
        <div class="container mx-auto px-4 text-center">
            <img src="Logo-formulario-dc.svg" alt="Descubre Cartagena" class="mx-auto mb-6 max-w-xs fade-in">
            <h2 class="text-3xl md:text-4xl font-bold mb-12 fade-in">Reserva tu Bote Ahora</h2>
            <?php if ($mostrarFormulario): ?>
            <form action="" method="POST" id="formulario" class="max-w-lg mx-auto bg-white p-8 rounded-lg shadow-lg fade-in">
                <div class="mb-4">
                    <label for="nombreCompleto" class="block text-gray-700 font-semibold mb-2">Nombre y Apellidos</label>
                    <input type="text" name="nombreCompleto" id="nombreCompleto" required value="<?php echo isset($_POST['nombreCompleto']) ? htmlspecialchars($_POST['nombreCompleto']) : ''; ?>" class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                    <?php if (isset($errors['nombreCompleto'])): ?>
                        <p class="text-red-500 text-sm mt-1"><?php echo $errors['nombreCompleto']; ?></p>
                    <?php endif; ?>
                </div>
                <div class="mb-4">
                    <label for="tipoEmbarcacion" class="block text-gray-700 font-semibold mb-2">Tipo de Embarcación</label>
                    <select name="tipoEmbarcacion" id="tipoEmbarcacion" required class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                        <option value="">Seleccione una opción</option>
                        <option value="Cualquiera" <?php echo isset($_POST['tipoEmbarcacion']) && $_POST['tipoEmbarcacion'] == 'Cualquiera' ? 'selected' : ''; ?>>Cualquiera</option>
                        <option value="Bote deportivo" <?php echo isset($_POST['tipoEmbarcacion']) && $_POST['tipoEmbarcacion'] == 'Bote deportivo' ? 'selected' : ''; ?>>Bote Deportivo</option>
                        <option value="Catamarán" <?php echo isset($_POST['tipoEmbarcacion']) && $_POST['tipoEmbarcacion'] == 'Catamarán' ? 'selected' : ''; ?>>Catamarán</option>
                        <option value="Yate" <?php echo isset($_POST['tipoEmbarcacion']) && $_POST['tipoEmbarcacion'] == 'Yate' ? 'selected' : ''; ?>>Yate</option>
                        <option value="Velero" <?php echo isset($_POST['tipoEmbarcacion']) && $_POST['tipoEmbarcacion'] == 'Velero' ? 'selected' : ''; ?>>Velero</option>
                    </select>
                    <?php if (isset($errors['tipoEmbarcacion'])): ?>
                        <p class="text-red-500 text-sm mt-1"><?php echo $errors['tipoEmbarcacion']; ?></p>
                    <?php endif; ?>
                </div>
                <div class="mb-4">
                    <label for="destino" class="block text-gray-700 font-semibold mb-2">Destino</label>
                    <select name="destino" id="destino" required class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                        <option value="">Seleccione un destino</option>
                        <option value="Islas del Rosario" <?php echo isset($_POST['destino']) && $_POST['destino'] == 'Islas del Rosario' ? 'selected' : ''; ?>>Islas del Rosario</option>
                        <option value="Bahía de Cartagena" <?php echo isset($_POST['destino']) && $_POST['destino'] == 'Bahía de Cartagena' ? 'selected' : ''; ?>>Bahía de Cartagena</option>
                    </select>
                    <?php if (isset($errors['destino'])): ?>
                        <p class="text-red-500 text-sm mt-1"><?php echo $errors['destino']; ?></p>
                    <?php endif; ?>
                </div>
                <div class="mb-4">
                    <label for="numeroPersonas" class="block text-gray-700 font-semibold mb-2">Número de Personas</label>
                    <input type="number" name="numeroPersonas" id="numeroPersonas" required value="<?php echo isset($_POST['numeroPersonas']) ? htmlspecialchars($_POST['numeroPersonas']) : '1'; ?>" min="1" max="100" step="1" class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                    <?php if (isset($errors['numeroPersonas'])): ?>
                        <p class="text-red-500 text-sm mt-1"><?php echo $errors['numeroPersonas']; ?></p>
                    <?php endif; ?>
                </div>
                <div class="mb-4">
                    <label for="fecha" class="block text-gray-700 font-semibold mb-2">Fecha del Paseo</label>
                    <input type="date" name="fecha" id="fecha" required value="<?php echo isset($_POST['fecha']) ? htmlspecialchars($_POST['fecha']) : ''; ?>" min="<?php echo date('Y-m-d'); ?>" class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                    <?php if (isset($errors['fecha'])): ?>
                        <p class="text-red-500 text-sm mt-1"><?php echo $errors['fecha']; ?></p>
                    <?php endif; ?>
                </div>
                <div class="mb-4">
                    <label for="whatsapp" class="block text-gray-700 font-semibold mb-2">WhatsApp</label>
                    <input type="tel" name="whatsapp" id="whatsapp" required value="<?php echo isset($_POST['whatsapp']) ? htmlspecialchars($_POST['whatsapp']) : ''; ?>" class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                    <?php if (isset($errors['whatsapp'])): ?>
                        <p class="text-red-500 text-sm mt-1"><?php echo $errors['whatsapp']; ?></p>
                    <?php endif; ?>
                </div>
                <button type="submit" class="bg-green-500 hover:bg-green-600 text-white font-bold py-3 px-8 rounded-full text-lg transition duration-300 shadow-lg hover:shadow-xl">Enviar Cotización</button>
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