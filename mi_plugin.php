<?php
/*
Plugin Name: Mi Plugin
Description: Plugin personalizado para crear usuarios y subir estudios.
Version: 1.0
Author: Jonathan Grauberg
*/

function mi_plugin_form() {
    ob_start();
    ?>
    <form action="" method="post" enctype="multipart/form-data">
        <label for="nombre_apellido">Nombre y Apellido:</label>
        <input type="text" name="nombre_apellido" required>

        <label for="usuario">Usuario:</label>
        <input type="text" name="usuario" required>

        <label for="contrasena">Contraseña:</label>
        <input type="password" name="contrasena" required>

        <?php foreach (['Estudios', 'Informes'] as $tipo): ?>
            <div class="drop-area" id="drop-area-<?php echo strtolower($tipo); ?>">
                <p>Arrastra y suelta tus archivos de <?php echo strtolower($tipo); ?> aquí o haz clic para seleccionarlos.</p>
                <input type="file" name="<?php echo strtolower($tipo); ?>[]" id="fileElem<?php echo $tipo; ?>" multiple accept=".pdf, .docx, .avi, .bmp, image/*" style="display:none;" />
                <label class="button" for="fileElem<?php echo $tipo; ?>">Seleccionar <?php echo $tipo; ?></label>
                <div id="file-info-<?php echo strtolower($tipo); ?>"></div>
            </div>
        <?php endforeach; ?>

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
    </style>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function () {
            <?php foreach (['Estudios', 'Informes'] as $tipo): ?>
                var dropArea<?php echo $tipo; ?> = $("#drop-area-<?php echo strtolower($tipo); ?>");
                var selectedFiles<?php echo $tipo; ?> = [];

                dropArea<?php echo $tipo; ?>.on('dragenter', function (e) {
                    e.stopPropagation();
                    e.preventDefault();
                    dropArea<?php echo $tipo; ?>.addClass('highlight');
                });

                dropArea<?php echo $tipo; ?>.on('dragover', function (e) {
                    e.stopPropagation();
                    e.preventDefault();
                });

                dropArea<?php echo $tipo; ?>.on('dragleave', function (e) {
                    e.stopPropagation();
                    e.preventDefault();
                    dropArea<?php echo $tipo; ?>.removeClass('highlight');
                });

                dropArea<?php echo $tipo; ?>.on('drop', function (e) {
                    e.preventDefault();
                    dropArea<?php echo $tipo; ?>.removeClass('highlight');

                    var files = e.originalEvent.dataTransfer.files;
                    $('#fileElem<?php echo $tipo; ?>')[0].files = files;

                    showFileInfo(files, selectedFiles<?php echo $tipo; ?>, 'file-info-<?php echo strtolower($tipo); ?>');
                });

                $('#fileElem<?php echo $tipo; ?>').on('change', function () {
                    var files = this.files;
                    showFileInfo(files, selectedFiles<?php echo $tipo; ?>, 'file-info-<?php echo strtolower($tipo); ?>');
                });
            <?php endforeach; ?>

            function showFileInfo(files, selectedFiles, fileInfoDivId) {
                var fileInfoDiv = $('#' + fileInfoDivId);
                fileInfoDiv.empty();

                if (files.length > 0) {
                    fileInfoDiv.append('<p>Archivos seleccionados:</p>');
                    fileInfoDiv.append('<ul>');

                    Array.prototype.push.apply(selectedFiles, files);

                    for (var i = 0; i < selectedFiles.length; i++) {
                        fileInfoDiv.append('<li>' + selectedFiles[i].name + '</li>');
                    }

                    fileInfoDiv.append('</ul>');
                } else {
                    fileInfoDiv.append('<p>No se han seleccionado archivos.</p>');
                }
            }
        });
    </script>
    <?php

    return ob_get_clean();
}
add_shortcode("mi_plugin_form", "mi_plugin_form");

function handle_mi_plugin_form() {
    if (isset($_POST['submit'])) {
        $nombre_apellido = sanitize_text_field($_POST['nombre_apellido']);
        $usuario = sanitize_text_field($_POST['usuario']);
        $contrasena = $_POST['contrasena'];
        $usuario_existente = isset($_POST['usuario_existente']) ? absint($_POST['usuario_existente']) : 0;

        if ($usuario_existente) {
            $user_id = $usuario_existente;
        } else {
            $user_id = wp_create_user($usuario, $contrasena, $usuario);

            if (is_wp_error($user_id)) {
                echo 'Error al crear el usuario: ' . $user_id->get_error_message();
                return;
            }

            update_user_meta($user_id, 'nombre_apellido', $nombre_apellido);
        }

        $tipos_archivos = ['Estudios', 'Informes'];
        foreach ($tipos_archivos as $tipo) {
            $archivos_info = get_user_meta($user_id, strtolower($tipo) . '_info', true);

            if (!is_array($archivos_info)) {
                $archivos_info = array();
            }

            // Procesar y guardar los archivos
            $upload_dir = wp_upload_dir();
            $archivos_dir = $upload_dir['basedir'] . '/' . strtolower($tipo) . '/' . $user_id . '/';

            wp_mkdir_p($archivos_dir);

            // Verificar si la cantidad de archivos seleccionados es igual a la cantidad de archivos que se están subiendo
            $num_files_selected = count($_FILES[strtolower($tipo)]['name']);
            $num_files_uploaded = count(array_filter($_FILES[strtolower($tipo)]['error'], function($error) { return $error == 0; }));

            if ($num_files_selected !== $num_files_uploaded) {
                echo 'Error: La cantidad de archivos de ' . strtolower($tipo) . ' seleccionados no coincide con la cantidad de archivos que se están subiendo.';
                return;
            }

            for ($i = 0; $i < $num_files_selected; $i++) {
                $file_name = sanitize_file_name($_FILES[strtolower($tipo)]['name'][$i]);
                $file_path = $archivos_dir . $file_name;

                // Mover el archivo del directorio temporal al directorio de destino
                if (move_uploaded_file($_FILES[strtolower($tipo)]['tmp_name'][$i], $file_path)) {
                    $archivos_info[] = array(
                        'file_name' => $file_name,
                        'timestamp' => current_time('timestamp'),
                    );
                } else {
                    echo 'Error al subir el archivo ' . $file_name . ' de ' . strtolower($tipo) . '.';
                    return;
                }
            }

            update_user_meta($user_id, strtolower($tipo) . '_info', $archivos_info);
        }

        echo 'Archivos agregados con éxito.';
    }
}
add_action("init", "handle_mi_plugin_form");

function download_file() {
    if (isset($_GET['download']) && $_GET['download'] !== '') {
        $file_name = urldecode($_GET['download']);
        $user_id = get_current_user_id();

        // Rutas para estudios e informes
        $estudios_path = wp_upload_dir()['basedir'] . DIRECTORY_SEPARATOR . 'estudios' . DIRECTORY_SEPARATOR . $user_id . DIRECTORY_SEPARATOR . $file_name;
        $informes_path = wp_upload_dir()['basedir'] . DIRECTORY_SEPARATOR . 'informes' . DIRECTORY_SEPARATOR . $user_id . DIRECTORY_SEPARATOR . $file_name;

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

        // Construir la URL del archivo en lugar de la ruta local
        if (file_exists($estudios_path)) {
            $file_url = wp_upload_dir()['baseurl'] . '/estudios/' . $user_id . '/' . $file_name;
        } elseif (file_exists($informes_path)) {
            $file_url = wp_upload_dir()['baseurl'] . '/informes/' . $user_id . '/' . $file_name;
        }

        header('Content-Description: File Transfer');
        header('Content-Type: ' . $mime_type);
        header('Content-Disposition: attachment; filename="' . $file_name . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . $file_size);
        readfile($file_url); // Usar la URL en lugar de la ruta local
        exit;
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
                                <tr>
                                    <td>
                                        <a href="<?php echo esc_url(add_query_arg(array('download' => urlencode($archivo['file_name'])), site_url())); ?>">
                                            <?php echo esc_html($archivo['file_name']); ?>
                                        </a>
                                    </td>
                                    <td><?php echo date('d/m/Y H:i:s', $archivo['timestamp']); ?></td>
                                </tr>
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
