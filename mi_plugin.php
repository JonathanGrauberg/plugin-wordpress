<?php
/*
Plugin Name: Mi Plugin
Description: Plugin personalizado para crear usuarios y subir estudios.
Version: 1.0
Author: Jonathan Grauberg
*/

function mi_plugin_form() {
    // Verificar si el usuario está autenticado
    if (!is_user_logged_in()) {
        // Redirigir al usuario a la página de inicio de sesión específica
        wp_redirect('https://imagengrupomedico.com/login-user/');
        exit;
    }

    ob_start();
    ?>
    <form action="" method="post" enctype="multipart/form-data" required>
         

        <label for="nombre">Nombre:</label>
        <input type="text" name="nombre" required>

        <label for="apellido">Apellido:</label>
        <input type="text" name="apellido" required>

        <label for="dni">DNI:</label>
        <input type="number" name="dni" required>

        <button type="button" id="generarCredenciales">Generar</button>

        <label for="nombre_apellido">Nombre y Apellido:</label>
        <input type="text" name="nombre_apellido" id="nombre_apellido" readonly required>

        <label for="usuario">Usuario:</label>
        <input type="text" name="usuario" id="usuario" readonly required>

        <label for="contrasena">Contraseña:</label>
        <div class="password-container">
            <input type="password" name="contrasena" id="contrasena" readonly required>
            <button type="button" id="verContrasena">Ver</button>
        </div>
        
        <input type="hidden" name="usuario_generado" id="usuario_generado" value="">

        <br>

        <label for="estudios">Subir Estudios:</label>
        <div class="drop-area" id="drop-area-estudios">
            <p>Arrastra y suelta tus archivos de estudios aquí o haz clic para seleccionarlos.</p>
            <input type="file" name="estudios[]" id="fileElemEstudios" multiple accept=".pdf, .docx, .avi, .bmp, dcm, dcim, image/*" style="display:none;" />
            <label class="button" for="fileElemEstudios">Seleccionar Estudios</label>
            <div id="file-info-estudios"></div>
        </div>

        <label for="informes">Subir Informes:</label>
        <div class="drop-area" id="drop-area-informes">
            <p>Arrastra y suelta tus archivos de informes aquí o haz clic para seleccionarlos.</p>
            <input type="file" name="informes[]" id="fileElemInformes" multiple accept=".pdf, .docx, .avi, .bmp, dcm, dcim, image/*" style="display:none;" />
            <label class="button" for="fileElemInformes">Seleccionar Informes</label>
            <div id="file-info-informes"></div>
        </div>

        <label for="usuario_existente">Seleccionar Usuario Existente:</label>
        <select name="usuario_existente">
            <option value="">Seleccionar Usuario</option>
            <?php
            foreach (get_users() as $user) {
                echo '<option value="' . esc_attr($user->ID) . '">' . esc_html($user->display_name) . '</option>';
            }
            ?>
        </select>

        <input type="submit" name="submit" value="Agregar Estudios e Informes">
    </form>

    <style>
        .password-container {
            position: relative;
            display: inline-block;
        }

        #verContrasena {
            position: absolute;
            top: 50%;
            right: 5px;
            transform: translateY(-50%);
            cursor: pointer;
        }

        .drop-area {
            border: 2px dashed #ccc;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            margin-top: 10px;
            background-color: #f9f9f9;
        }

        .drop-area.highlight {
            background-color: #eaf7ea;
            border-color: #5cb85c;
        }

        form {
            max-width: 600px;
            margin: 0 auto;
        }

        @media (max-width: 600px) {
            form {
                max-width: 100%;
            }

            label, input {
                display: block;
                margin-bottom: 10px;
            }

            .password-container {
                width: 100%;
            }

            #verContrasena {
                right: 0;
            }

            .drop-area {
                margin-top: 10px;
            }
        }
    </style>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        document.getElementById('generarCredenciales').addEventListener('click', function() {
            var nombre = document.querySelector('input[name="nombre"]').value;
            var apellido = document.querySelector('input[name="apellido"]').value;
            var dni = document.querySelector('input[name="dni"]').value;
            
            // Verificar que los campos obligatorios no estén vacíos
            if (nombre.trim() === '' || apellido.trim() === '' || dni.trim() === '') {
            alert('Debes completar los campos Nombre, Apellido y DNI.');
            return;
            }

            // Lógica para generar usuario y contraseña
            var usuario = (nombre.charAt(0) + apellido.charAt(0) + dni).toUpperCase();
            var contrasena = 'IGM' + dni;

        // Mostrar alerta con los detalles del usuario
        var confirmacion = confirm('Usuario generado:\nUsuario: ' + usuario + '\nContraseña: ' + contrasena + '\n\n¿Aceptar o Cancelar?');

        if (confirmacion) {
            // El usuario ha confirmado (aceptar)
            document.getElementById('usuario').value = usuario;
            document.getElementById('contrasena').value = contrasena;
            document.getElementById('usuario_generado').value = usuario;
            var nombreApellidoInput = document.getElementById('nombre_apellido');
            nombreApellidoInput.value = nombre + ' ' + apellido;
        } else {
            // El usuario ha elegido modificar (cancelar)
            document.getElementById('nombre').value = '';
            document.getElementById('apellido').value = '';
            document.getElementById('dni').value = '';
            document.getElementById('usuario').value = '';
            document.getElementById('contrasena').value = '';
            document.getElementById('usuario_generado').value = '';
            document.getElementById('nombre_apellido').value = '';
        }
    });

        document.getElementById('verContrasena').addEventListener('click', function () {
            var inputTipoContraseña = document.getElementById('contrasena');
            var tipo = inputTipoContraseña.type;

            if (tipo === 'password') {
                inputTipoContraseña.type = 'text';
            } else {
                inputTipoContraseña.type = 'password';
            }
        });

        <?php foreach (['Estudios', 'Informes'] as $tipo): ?>
            var dropArea<?php echo $tipo; ?> = document.getElementById('drop-area-<?php echo strtolower($tipo); ?>');
            var selectedFiles<?php echo $tipo; ?> = [];

            dropArea<?php echo $tipo; ?>.addEventListener('dragenter', function (e) {
                e.stopPropagation();
                e.preventDefault();
                dropArea<?php echo $tipo; ?>.classList.add('highlight');
            });

            dropArea<?php echo $tipo; ?>.addEventListener('dragover', function (e) {
                e.stopPropagation();
                e.preventDefault();
            });

            dropArea<?php echo $tipo; ?>.addEventListener('dragleave', function (e) {
                e.stopPropagation();
                e.preventDefault();
                dropArea<?php echo $tipo; ?>.classList.remove('highlight');
            });

            dropArea<?php echo $tipo; ?>.addEventListener('drop', function (e) {
                e.preventDefault();
                dropArea<?php echo $tipo; ?>.classList.remove('highlight');

                var files = e.dataTransfer.files;
                document.getElementById('fileElem<?php echo $tipo; ?>').files = files;

                showFileInfo(files, selectedFiles<?php echo $tipo; ?>, 'file-info-<?php echo strtolower($tipo); ?>');
            });

            document.getElementById('fileElem<?php echo $tipo; ?>').addEventListener('change', function () {
                var files = this.files;
                showFileInfo(files, selectedFiles<?php echo $tipo; ?>, 'file-info-<?php echo strtolower($tipo); ?>');
            });
        <?php endforeach; ?>

        function showFileInfo(files, selectedFiles, fileInfoDivId) {
            var fileInfoDiv = document.getElementById(fileInfoDivId);
            fileInfoDiv.innerHTML = '';

            if (files.length > 0) {
                fileInfoDiv.innerHTML += '<p>Archivos seleccionados:</p>';
                fileInfoDiv.innerHTML += '<ul>';

                Array.prototype.push.apply(selectedFiles, files);

                for (var i = 0; i < selectedFiles.length; i++) {
                    fileInfoDiv.innerHTML += '<li>' + selectedFiles[i].name + '</li>';
                }

                fileInfoDiv.innerHTML += '</ul>';
            } else {
                fileInfoDiv.innerHTML += '<p>No se han seleccionado archivos.</p>';
            }
        }
    });
</script>

    <?php

    return ob_get_clean();
}
add_shortcode("mi_plugin_form", "mi_plugin_form");




function handle_mi_plugin_form() {
    $error_message = ''; // Variable para almacenar el mensaje de error
    

    if (isset($_POST['submit'])) {
        // Verificar otros campos del formulario
        $nombre_apellido = sanitize_text_field($_POST['nombre_apellido']);
        $usuario = sanitize_text_field($_POST['usuario']);
        $contrasena = $_POST['contrasena'];
        $usuario_existente = isset($_POST['usuario_existente']) ? absint($_POST['usuario_existente']) : 0;

        // Verificar si se han subido archivos
        $estudios_subidos = !empty($_FILES['estudios']['name'][0]);
        $informes_subidos = !empty($_FILES['informes']['name'][0]);

        $usuario_generado = sanitize_text_field($_POST['usuario_generado']);

        // Verificar si el usuario ya existe sin seleccionar uno existente
        if (!$usuario_existente && username_exists($usuario_generado)) {
            $error_message = 'El usuario ya existe. Por favor, selecciónalo desde el menú -Seleccionar Usuario Existente-.';
        } else {
            if (!$estudios_subidos && !$informes_subidos) {
                $error_message = 'Debes subir al menos un estudio o informe.';
            } else {
                if ($usuario_existente) {
                    $user_id = $usuario_existente;
                } else {
                    $user_id = wp_create_user($usuario, $contrasena, $usuario);

                    if (is_wp_error($user_id)) {
                        $error_message = 'Error al crear el usuario: ' . $user_id->get_error_message();
                    } else {
                        update_user_meta($user_id, 'nombre_apellido', $nombre_apellido);
                    }
                }

                // Lógica para subir estudios si se han subido
                if ($estudios_subidos) {
                    $archivos_info_estudios = handle_file_upload($user_id, 'estudios');
                }

                // Lógica para subir informes si se han subido
                if ($informes_subidos) {
                    $archivos_info_informes = handle_file_upload($user_id, 'informes');
                }

                // Si no hay errores hasta este punto, mostrar la alerta de éxito
                if (empty($error_message)) {
                    echo '<script>alert("Archivos cargados con éxito.");</script>';
                }

                // Verificar si solo se ha subido un tipo de archivo y mostrar alerta
                if (($estudios_subidos && !$informes_subidos) || (!$estudios_subidos && $informes_subidos)) {
                    echo '<script>alert("Se ha subido solo un tipo de archivo. Te recomendamos subir ambos estudios e informes para una mejor gestión.");</script>';
                }
            }
        }
    }

    // Mostrar el mensaje de error mediante JavaScript solo si hay un error
    if ($error_message) {
        echo '<script>alert("' . esc_js($error_message) . '");</script>';
    }
}







function handle_file_upload($user_id, $tipo_archivo) {
    $archivos_info = get_user_meta($user_id, strtolower($tipo_archivo) . '_info', true);

    if (!is_array($archivos_info)) {
        $archivos_info = array();
    }

    // Obtener información del directorio de carga
    $upload_dir = wp_upload_dir();
    $archivos_dir = $upload_dir['basedir'] . '/' . strtolower($tipo_archivo) . '/' . $user_id . '/';

    wp_mkdir_p($archivos_dir);

    $num_files_selected = count($_FILES[strtolower($tipo_archivo)]['name']);

    for ($i = 0; $i < $num_files_selected; $i++) {
        $file_name = sanitize_file_name($_FILES[strtolower($tipo_archivo)]['name'][$i]);
        $file_path = $archivos_dir . $file_name;

        // Verificar si el archivo ya existe y agregar un prefijo con el timestamp si es necesario
        if (file_exists($file_path)) {
            $timestamp = current_time('timestamp');
            $file_name = $timestamp . '_' . $file_name;
            $file_path = $archivos_dir . $file_name;
        }

        if (move_uploaded_file($_FILES[strtolower($tipo_archivo)]['tmp_name'][$i], $file_path)) {
            $archivos_info[] = array(
                'file_name' => $file_name,
                'timestamp' => current_time('timestamp'),
            );
        } else {
            echo '<script>';
            echo 'alert("¡Paciente creado pero falta subir: ' . strtolower($tipo_archivo) . '!");';
            echo '</script>';
            
            return;
        }
    }

    update_user_meta($user_id, strtolower($tipo_archivo) . '_info', $archivos_info);

    return $archivos_info;
}






add_action("init", "handle_mi_plugin_form");

function download_file() {
    if (isset($_GET['download']) && $_GET['download'] !== '') {
        $file_name = urldecode($_GET['download']);
        $user_id = isset($_GET['paciente_id']) ? absint($_GET['paciente_id']) : get_current_user_id();

        // Rutas para estudios e informes
        $estudios_dir = wp_upload_dir()['basedir'] . DIRECTORY_SEPARATOR . 'estudios' . DIRECTORY_SEPARATOR;
        $informes_dir = wp_upload_dir()['basedir'] . DIRECTORY_SEPARATOR . 'informes' . DIRECTORY_SEPARATOR;

        $estudios_path = $estudios_dir . $user_id . DIRECTORY_SEPARATOR . $file_name;
        $informes_path = $informes_dir . $user_id . DIRECTORY_SEPARATOR . $file_name;

        if (file_exists($estudios_path)) {
            $file_path = $estudios_path;
        } elseif (file_exists($informes_path)) {
            $file_path = $informes_path;
        } else {
            echo 'Error: El archivo no existe en ninguna de las rutas especificadas.<br>';
            return;
        }

        $file_info = wp_check_filetype($file_name);

        if ($file_info['ext']) {
            $mime_type = $file_info['type'];
        } else {
            // Si no se puede determinar el tipo MIME, establecerlo como binario genérico
            $mime_type = 'application/octet-stream';
        }

        $file_size = filesize($file_path);

        // Verificar la existencia del archivo antes de enviarlo
        if (file_exists($file_path)) {
            header('Content-Description: File Transfer');
            header('Content-Type: ' . $mime_type);
            header('Content-Disposition: attachment; filename="' . $file_name . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . $file_size);
            readfile($file_path); // Usar la ruta local en lugar de la URL
            exit;
        } else {
            echo 'Error: El archivo no existe en la ruta especificada.<br>';
            return;
        }
    }
}

add_action('init', 'download_file');





function add_nombre_column($columns) {
    $columns['nombre_apellido'] = 'Nombre';
    return $columns;
}

add_filter('manage_users_columns', 'add_nombre_column');

function show_nombre_column($value, $column_name, $user_id) {
    if ($column_name === 'nombre_apellido') {
        return get_user_meta($user_id, 'nombre_apellido', true);
    }
    return $value;
}

add_action('manage_users_custom_column', 'show_nombre_column', 10, 3);

function mostrar_estudios_informes_usuario() {
    ob_start();

    if (is_user_logged_in()) {
        $user_id = get_current_user_id();
        $tipos_archivos = ['Estudios', 'Informes'];

        foreach ($tipos_archivos as $tipo) {
            $archivos_info = get_user_meta($user_id, strtolower($tipo) . '_info', true);

            if (is_array($archivos_info) && !empty($archivos_info)) {
                ?>
                <h2>Mis <?php echo $tipo; ?></h2>
                <div class="<?php echo strtolower($tipo); ?>-table-container">
                    <table class="<?php echo strtolower($tipo); ?>-table">
                        <thead>
                            <tr>
                                <th>Nombre del Archivo</th>
                                <th>Fecha de Subida</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($archivos_info as $archivo) : ?>
    <?php
    // Construir la ruta del archivo
    $file_path = wp_upload_dir()['basedir'] . DIRECTORY_SEPARATOR . strtolower($tipo) . DIRECTORY_SEPARATOR . $user_id . DIRECTORY_SEPARATOR . $archivo['file_name'];
    ?>
    <?php if (file_exists($file_path)) : ?>
        <tr>
            <td>
                <a href="<?php echo esc_url(add_query_arg(array('download' => urlencode($archivo['file_name'])), site_url())); ?>">
                    <?php echo esc_html($archivo['file_name']); ?>
                </a>
            </td>
            <td><?php echo date('d/m/Y H:i:s', $archivo['timestamp']); ?></td>
        </tr>
    <?php endif; ?>
<?php endforeach; ?>

                        </tbody>
                    </table>
                </div>
                <style>
                    .<?php echo strtolower($tipo); ?>-table-container {
                        overflow-x: auto;
                    }

                    .<?php echo strtolower($tipo); ?>-table {
                        width: 100%;
                        border-collapse: collapse;
                        margin-top: 10px;
                    }

                    .<?php echo strtolower($tipo); ?>-table th,
                    .<?php echo strtolower($tipo); ?>-table td {
                        border: 1px solid #ddd;
                        padding: 8px;
                        text-align: left;
                    }

                    @media (max-width: 600px) {
                        .<?php echo strtolower($tipo); ?>-table th,
                        .<?php echo strtolower($tipo); ?>-table td {
                            font-size: 14px;
                        }
                    }
                </style>
            <?php
            } else {
                echo 'Aún no hay ' . strtolower($tipo) . ' cargados.';
            }
        }
    } else {
        echo 'Debes iniciar sesión para ver tus estudios e informes. Dirígete a la parte superior y presiona en "iniciar sesión"';
    }

    return ob_get_clean();
}

add_shortcode("mostrar_estudios_informes_usuario", "mostrar_estudios_informes_usuario");






function seleccionar_paciente_form() {
    ob_start();
    ?>
    <form action="" method="post">
        <label for="busqueda_paciente">Buscar Paciente por Usuario o DNI:</label>
        <input type="text" name="busqueda_paciente" id="busqueda_paciente" value="<?php echo isset($_POST['busqueda_paciente']) ? esc_attr($_POST['busqueda_paciente']) : ''; ?>">
        
        <input type="submit" name="buscar_paciente" value="Buscar">
        <br>
    </form>

    <?php
    // Verificar si se ha presionado el botón de búsqueda y buscar usuarios
    if (isset($_POST['buscar_paciente'])) {
        $busqueda = isset($_POST['busqueda_paciente']) ? sanitize_text_field($_POST['busqueda_paciente']) : '';

        $args = array(
            'search'         => "*{$busqueda}*",
            'search_columns' => array('user_login', 'user_nicename', 'display_name', 'user_email'),
        );

        $users = get_users($args);

        // Mostrar la sección "Seleccionar Paciente" solo si se encontraron usuarios
        if (!empty($users)) {
    ?>
            <form action="" method="post">
                <label for="paciente_existente">Seleccionar Paciente:</label>
                <select name="paciente_existente">
                    <?php
                    foreach ($users as $user) {
                        echo '<option value="' . esc_attr($user->ID) . '">' . esc_html($user->display_name) . '</option>';
                    }
                    ?>
                </select>
                
                <input type="submit" name="seleccionar_paciente" value="Seleccionar Paciente">
            </form>
    <?php
        }
    }

    if (isset($_POST['seleccionar_paciente']) && isset($_POST['paciente_existente'])) {
        $paciente_id = absint($_POST['paciente_existente']);
        $user_data = get_userdata($paciente_id);
        $nombre_paciente = get_user_meta($paciente_id, 'nombre_apellido', true);

        if ($nombre_paciente) {
            echo '<h2>Detalles del Paciente: ' . esc_html($nombre_paciente) . '</h2>';
        } else {
            echo '<h2>Detalles del Paciente: Usuario ID ' . esc_html($user_data->display_name) . '</h2>';
        }

        // Mostrar archivos existentes y permitir eliminación
        mostrar_estudios_informes_paciente($paciente_id);
    }
    return ob_get_clean();
}
add_shortcode("seleccionar_paciente_form", "seleccionar_paciente_form");







function mostrar_estudios_informes_paciente($user_id) {
    $tipos_archivos = ['Estudios', 'Informes'];

    foreach ($tipos_archivos as $tipo) {
        $archivos_info = get_user_meta($user_id, strtolower($tipo) . '_info', true);

        ?>
        <h3><?php echo 'Sus ' . $tipo; ?></h3>
        <div class="<?php echo strtolower($tipo); ?>-table-container">
            <table class="<?php echo strtolower($tipo); ?>-table">
                <thead>
                    <tr>
                        <th>Nombre del Archivo</th>
                        <th>Fecha de Subida</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($archivos_info as $archivo) : ?>
                        <?php
                        $file_path = wp_upload_dir()['basedir'] . DIRECTORY_SEPARATOR . strtolower($tipo) . DIRECTORY_SEPARATOR . $user_id . DIRECTORY_SEPARATOR . $archivo['file_name'];
                        ?>
                        <?php if (file_exists($file_path)) : ?>
                            <tr>
                                <td>
                                    <a href="<?php echo esc_url(add_query_arg(array('download' => urlencode($archivo['file_name']), 'paciente_id' => $user_id), site_url())); ?>">
                                        <?php echo esc_html($archivo['file_name']); ?>
                                    </a>
                                </td>
                                <td><?php echo date('d/m/Y H:i:s', $archivo['timestamp']); ?></td>
                                <td>
                                    <a href="<?php echo esc_url(add_query_arg(array('eliminar_archivo' => urlencode($archivo['file_name']), 'paciente_id' => $user_id), site_url())); ?>">
                                        Eliminar
                                    </a>
                                </td>
                            </tr>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php
            $user_name = get_user_by('ID', $user_id)->display_name;
            echo '<a href="https://imagengrupomedico.com/agregar-archivos/?user_id=' . $user_id . '"><button>Agregar Archivos</button></a>';
            ?>
        </div>
        <?php
    }
}






function eliminar_archivo() {
    if (isset($_GET['eliminar_archivo']) && isset($_GET['paciente_id'])) {
        $file_name = urldecode($_GET['eliminar_archivo']);
        $paciente_id = absint($_GET['paciente_id']);

        // Rutas para estudios e informes
        $estudios_path = wp_upload_dir()['basedir'] . DIRECTORY_SEPARATOR . 'estudios' . DIRECTORY_SEPARATOR . $paciente_id . DIRECTORY_SEPARATOR . $file_name;
        $informes_path = wp_upload_dir()['basedir'] . DIRECTORY_SEPARATOR . 'informes' . DIRECTORY_SEPARATOR . $paciente_id . DIRECTORY_SEPARATOR . $file_name;

        // Construir la URL del archivo en lugar de la ruta local
        if (file_exists($estudios_path)) {
            $file_url = wp_upload_dir()['baseurl'] . '/estudios/' . $paciente_id . '/' . $file_name;
        } elseif (file_exists($informes_path)) {
            $file_url = wp_upload_dir()['baseurl'] . '/informes/' . $paciente_id . '/' . $file_name;
        } else {
            echo 'Error: El archivo no existe en ninguna de las rutas especificadas.<br>';
            return;
        }

        // Eliminar archivo
        unlink($estudios_path);
        unlink($informes_path);
        
        
        wp_redirect('https://imagengrupomedico.com/consultar-pacientes');
        exit;
        
    }
}
add_action('init', 'eliminar_archivo');



function formulario_carga_archivos_shortcode() {
    ob_start();

    
    $user_id = isset($_GET['user_id']) ? absint($_GET['user_id']) : 0;

   
    if (!$user_id || !get_user_by('ID', $user_id)) {
        echo '<p>Usuario no válido.</p>';
        return;
    }

    $user_name = get_user_by('ID', $user_id)->display_name;

    if (isset($_POST['submit'])) {
        // Lógica para subir estudios si se han subido
        if (!empty($_FILES['estudios']['name'][0])) {
            $archivos_info_estudios = handle_file_upload_for_selected_patient($user_id, 'estudios');
        }

        // Lógica para subir informes si se han subido
        if (!empty($_FILES['informes']['name'][0])) {
            $archivos_info_informes = handle_file_upload_for_selected_patient($user_id, 'informes');
        }

        // Si no hay errores hasta este punto, mostrar la alerta de éxito
        echo '<script>alert("Archivos cargados con éxito.");</script>';
    }

    // Mostrar el formulario de carga de archivos
    ?>
       <div class="formulario-carga-archivos">
            <h2>Agregar archivos al paciente: <?php echo esc_html($user_name); ?></h2>
            <?php foreach (['Estudios', 'Informes'] as $tipo): ?>
                <div class="drop-area" id="drop-area-<?php echo strtolower($tipo); ?>">
                <form class="my-form">
                    <p>Arrastra y suelta tus archivos aquí o haz clic para seleccionar.</p>
                    <input type="file" id="fileElem<?php echo $tipo; ?>" multiple accept=".pdf, .docx, .avi, .bmp, image/*" style="display: none;" />
                    <label class="button" for="fileElem<?php echo $tipo; ?>">Seleccionar archivos</label>
                    <div id="file-info-<?php echo strtolower($tipo); ?>"></div>
            </div>
        <?php endforeach; ?>
                <input type="submit" name="submit" value="Agregar Estudios e Informes">

    </form>
    <style>
        .drop-area {
            border: 2px dashed #ccc;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            margin-top: 10px;
            background-color: #f9f9f9;
        }

        .drop-area.highlight {
            background-color: #eaf7ea;
            border-color: #5cb85c;
        }

        form {
            max-width: 600px;
            margin: 0 auto;
        }

        @media (max-width: 600px) {
            form {
                max-width: 100%;
            }

            label, input {
                display: block;
                margin-bottom: 10px;
            }
            .drop-area {
                margin-top: 10px;
            }
        }
    </style>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        <?php foreach (['Estudios', 'Informes'] as $tipo): ?>
            var dropArea<?php echo $tipo; ?> = document.getElementById('drop-area-<?php echo strtolower($tipo); ?>');
            var selectedFiles<?php echo $tipo; ?> = [];

            dropArea<?php echo $tipo; ?>.addEventListener('dragenter', function (e) {
                e.stopPropagation();
                e.preventDefault();
                dropArea<?php echo $tipo; ?>.classList.add('highlight');
            });

            dropArea<?php echo $tipo; ?>.addEventListener('dragover', function (e) {
                e.stopPropagation();
                e.preventDefault();
            });

            dropArea<?php echo $tipo; ?>.addEventListener('dragleave', function (e) {
                e.stopPropagation();
                e.preventDefault();
                dropArea<?php echo $tipo; ?>.classList.remove('highlight');
            });

            dropArea<?php echo $tipo; ?>.addEventListener('drop', function (e) {
                e.preventDefault();
                dropArea<?php echo $tipo; ?>.classList.remove('highlight');
                var files = e.dataTransfer.files;
                document.getElementById('fileElem<?php echo $tipo; ?>').files = files;
                showFileInfo(files, selectedFiles<?php echo $tipo; ?>, 'file-info-<?php echo strtolower($tipo); ?>');
            });

            document.getElementById('fileElem<?php echo $tipo; ?>').addEventListener('change', function () {
                var files = this.files;
                showFileInfo(files, selectedFiles<?php echo $tipo; ?>, 'file-info-<?php echo strtolower($tipo); ?>');
            });
        <?php endforeach; ?>

        function showFileInfo(files, selectedFiles, fileInfoDivId) {
            var fileInfoDiv = document.getElementById(fileInfoDivId);
            fileInfoDiv.innerHTML = '';

            if (files.length > 0) {
                fileInfoDiv.innerHTML += '<p>Archivos seleccionados:</p>';
                fileInfoDiv.innerHTML += '<ul>';

                Array.prototype.push.apply(selectedFiles, files);

                for (var i = 0; i < selectedFiles.length; i++) {
                    fileInfoDiv.innerHTML += '<li>' + selectedFiles[i].name + '</li>';
                }

                fileInfoDiv.innerHTML += '</ul>';
            } else {
                fileInfoDiv.innerHTML += '<p>No se han seleccionado archivos.</p>';
            }
        }
    });
    </script>
    <?php
    return ob_get_clean();
}

// Registra el shortcode
add_shortcode('formulario_carga_archivos', 'formulario_carga_archivos_shortcode');


