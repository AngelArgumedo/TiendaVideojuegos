<?php
// src/core/patterns/Observer.php

/**
 * La interfaz SplSubject representa al objeto que está siendo observado.
 * PHP ya nos proporciona esta interfaz estándar.
 */
interface Subject {
    // Añade un observador a la lista.
    public function attach(Observer $observer);

    // Elimina un observador de la lista.
    public function detach(Observer $observer);

    // Notifica a todos los observadores sobre un evento.
    public function notify();
}

/**
 * La interfaz SplObserver representa a cualquier objeto que quiera ser notificado.
 * PHP también nos proporciona esta interfaz.
 */
interface Observer {
    // Recibe la actualización del sujeto.
    public function update(Subject $subject);
}

// Nota: Técnicamente PHP ya incluye las interfaces `SplSubject` y `SplObserver`. Las definimos aquí explícitamente para mayor claridad y para tener un archivo centralizado para nuestro patró

?>
