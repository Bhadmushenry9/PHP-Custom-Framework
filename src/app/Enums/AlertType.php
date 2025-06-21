<?php
// app/Enums/AlertType.php
namespace App\Enums;

enum AlertType: string
{
    case Success = 'success';
    case Error = 'error';
    case Warning = 'warning';
    case Info = 'info';
}
