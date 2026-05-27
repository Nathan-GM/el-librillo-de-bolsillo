// Ref: https://regexr.com/ para probar todas y cada una de las expresiones regulares.

// EXPRESIONES - REGISTER.php
// Dirección de correo
// Comprueba que:
//  - No haya caracteres en blanco (tabulaciones, espacios o saltos de linea) ni @
//  - Le siga el caracter '@'
//  - No haya caracteres en blanco (tabulaciones, espacios o saltos de linea) ni @
//  - Le siga el caracter '.'
//  - No haya caracteres en blanco (tabulaciones, espacios o saltos de linea) ni @
// Ejemplo valido: nathan@gmail.com     ||      ejemplo no valido: nath@n@gmail.com
// 
var correoRegEx = /^[^\s@]+@[^\s@]+\.[^\s@]+$/

// Contraseña
// Comprobará que exista minimo:
//  - Minuscula
//  - Mayuscula
//  - Número
//  - Caracter especial
//  - 6 caracteres
var passwordRegEx = /(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*\W).{6,}/

// Numero de telefono. No tiene en cuenta los prefijos.
var numeroDeTelefono = /^\d{9}$/


// EXPRESIONES - PAYMENTFORM.php

// Validador de número de tarjeta. Comprueba que haya entre 14 o 16 números.
var numeroTarjeta = /^\d{14,16}$/
// Comprueba que el numero privado tenga 3 digitos.
var numPrivadoTarjeta = /^\d{3}$/

// REF1: https://regex101.com/library/AFarfB
// REF2: https://stackoverflow.com/questions/20430391/regular-expression-to-match-credit-card-expiration-date

// Comprueba que la fecha expiración sea valida de la siguiente manera:
// Comprueba que sea 01, 02... hasta 09 O SI NO sea 10, 11 o 12. De esta forma
// se asegura que no se pongan meses invalidos.
// Tras ello con \/ se representa la /
// Y finalmente se indican los 2 digitos del año.
var fechaExpiracion = /^(0[0-9]|1[0-2])\/\d{2}/;