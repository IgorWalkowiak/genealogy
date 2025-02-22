<?php

declare(strict_types=1);

return [
    'backup'        => 'Respaldo',
    'backups'       => 'Copias de seguridad',
    'no_data'       => 'No hay copias de seguridad disponibles.',
    'create'        => 'Nueva copia de seguridad',
    'download'      => 'Descargar',
    'delete'        => 'Borrar',
    'delete_backup' => 'esta copia de seguridad',
    'id'            => '#',
    'file'          => 'Archivo',
    'size'          => 'Tamaño',
    'date'          => 'Fecha',
    'age'           => 'Edad',
    'actions'       => 'Comportamiento',
    'backup_daily'  => 'Las copias de seguridad se crean automáticamente diariamente (a las ' . config('app.backup.daily_run') . ' hora).',
    'backup_email'  => 'Se enviará un correo electrónico a la dirección de correo electrónico de su aplicación después de cada copia de seguridad.',
    'backup_cron_1' => 'Las copias de seguridad se pueden automatizar (ejecutar diariamente) emitiendo el siguiente trabajo cron en su servidor de producción :',
    'backup_cron_2' => '* * * * * cd /ruta_a_su_aplicación && programación artesanal php:run >> /dev/null 2>&1',
    'created'       => 'Se guardó la nueva copia de seguridad.',
    'deleted'       => 'se elimina.',
    'downloading'   => 'Se inicia la descarga.',
    'failed'        => 'La copia de seguridad falló.',
    'not_found'     => 'No se encontró la copia de seguridad.',
];
