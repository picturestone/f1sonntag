<?php

namespace App\Service;

use App\Dto\ToastDto;

class ToastFactory
{
    // TODO move to src/toast
    public static function generateCustomSuccessToast(string $text) {
        $toast = new ToastDto();
        $toast->setText($text);
        $toast->setState(ToastDto::STATE_SUCCESS);

        return $toast;
    }

    public static function generateCustomErrorToast(string $text) {
        $toast = new ToastDto();
        $toast->setText($text);
        $toast->setState(ToastDto::STATE_ERROR);

        return $toast;
    }

    public static function generateSaveSuccessfulToast() {
        $toast = new ToastDto();
        $toast->setText('Speichern erfolgreich');
        $toast->setState(ToastDto::STATE_SUCCESS);

        return $toast;
    }

    public static function generateDeleteSuccessfulToast() {
        $toast = new ToastDto();
        $toast->setText('Löschen erfolgreich');
        $toast->setState(ToastDto::STATE_SUCCESS);

        return $toast;
    }
}
