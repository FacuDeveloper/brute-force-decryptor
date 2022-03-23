<?php
  // 1. Tomar el mensaje de entrada
  // 2. Realizar el descifrado Cesar del mensaje de entrada con cada desplazamiento
  // 3. Verificar si las palabras de uno de los mensajes obtenidos del descifrado Cesar estan en un archivo de texto de diccionario
  // 4. Si lo estan, se descifro el mensaje de entrada

  // The global $_POST variable allows you to access the data sent with the POST method by name
  // To access the data sent with the GET method, you can use $_GET
  $message  = htmlspecialchars($_POST['message']);

  $alphabet = [
    '0' => 'a',
    '1' => 'b',
    '2' => 'c',
    '3' => 'd',
    '4' => 'e',
    '5' => 'f',
    '6' => 'g',
    '7' => 'h',
    '8' => 'i',
    '9' => 'j',
    '10' => 'k',
    '11' => 'l',
    '12' => 'm',
    '13' => 'n',
    '14' => 'o',
    '15' => 'p',
    '16' => 'q',
    '17' => 'r',
    '18' => 's',
    '19' => 't',
    '20' => 'u',
    '21' => 'v',
    '22' => 'w',
    '23' => 'x',
    '24' => 'y',
    '25' => 'z'];

  if (empty($message)) {
    exit("El mensaje no debe estar vacio.");
  }

  // Comprueba que el mensaje de entrada solo contenga letras en minuscula y espacios en blanco
  if (!preg_match("/^[a-z\s]+$/", $message)) {
    exit("No se permiten letras mayusculas, caracteres numericos, caracteres de puntuacion ni caracteres especiales (tilde, dieresis, arroba, etc.). Solo se permiten letras minusculas y espacios en blanco. La letra ñ no esta permitida.");
  }

  main($alphabet, $message);

  /**
  * @param array $alphabet
  * @param string $message mensaje de entrada
  */
  function main($alphabet, $message) {
    // Elimina los espacios en blanco del principio y el final del mensaje de entrada
    $message = trim($message);
    $decrypted_message;

    // Arreglo en el cual cada elemento es un mensaje desencriptado con un desplazamiento dado
    $decrypted_messages_generated = [];

    // Realiza el descifrado Cesar del mensaje de entrada con cada desplazamiento, el cual va desde 1 hasta el tamaño del alfabeto
    for ($offset = 1; $offset <= count($alphabet); $offset++) {
      $decrypted_message = decode($alphabet, $offset, $message);
      array_push($decrypted_messages_generated, $decrypted_message);
    }

    findDecryptedMessage($decrypted_messages_generated, $message);
  }

  /**
  * Busca el mensaje desencriptado en las cadenas generadas (mensaje de entrada desencriptado
  * con cada desplazamiento) por el descifrado Cesar
  *
  * @param array $decrypted_messages_generated contiene las cadenas resultantes del descifrado
  * Cesar, realizado con cada desplazamiento, del mensaje de entrada encriptado
  */
  function findDecryptedMessage($decrypted_messages_generated, $message) {
    $decrypted_word_array = [];
    $decrypted_message;

    foreach ($decrypted_messages_generated as $key => $given_decrypted_message) {
      /* Obtiene un arreglo en el cual cada elemento es una palabra del mensaje de entrada
      desencriptado con un desplazamiento dado */
      $decrypted_word_array = explode(" ", $given_decrypted_message);

      /* Obtiene el mensaje desencriptado, siempre y cuando el mensaje de entrada encriptado
      haya sido efectivamente desencriptado */
      $decrypted_message = findDecryptedWords($decrypted_word_array);

      /* El mensaje desencriptado tiene la misma longitud que el mensaje de entrada encriptado, por lo
      tanto, si el mensaje desencriptado encontrado por este programa tiene la misma longitud que el mensaje
      de entrada encriptado, efectivamente se descifro el mensaje de entrada */
      if (strlen($decrypted_message) == strlen(str_replace(" ", "", $message))) {
        // echo "<br />Mensaje desencriptado: ";
        echo "Mensaje desencriptado: ";
        echo $given_decrypted_message;
        exit();
      }

    }

    /* Si el mensaje desencriptado por este programa tiene una longitud menor a la del
    mensaje de entrada encriptado, no se descifro el mensaje de entrada */
    echo "Mensaje no desencriptado.";
  }

  /**
  * Comprueba si las palabras de una cadena generada por el descifrado Cesar
  * estan en un archivo de texto de diccionario
  *
  * @param array $decrypted_word_array cada elemento de este arreglo es cada una de las
  * palabras en formato de descifrado Cesar de un mensaje de entrada
  * @return string mensaje de entrada desencriptado, siempre y cuando dicho
  * mensaje haya sido efectivamente desencriptado
  */
  function findDecryptedWords($decrypted_word_array) {
    $decrypted_message;
    $word_dictionary;
    $file_record;

    $dictionary_file = fopen("dictionary.txt", "r");

    if (!$dictionary_file) {
      exit("Hubo un error al abrir el archivo de texto de diccionario.");
    }

    /* Comprueba que cada palabra de una cadena generada a partir del descifrado Cesar,
    este en el archivo de texto de diccionario */
    foreach ($decrypted_word_array as $key => $word) {

      // Recorre el archivo de texto de diccionario leyendo cada uno de sus registros
      while ($file_record = fgets($dictionary_file)) {

        if (!$file_record) {
          exit("Hubo un error al leer un registro del archivo de texto de diccionario.");
        }

        /* Elimina los espacios en blanco del principio y el final que pueda
        tener una palabra del archivo de texto de diccionario */
        $word_dictionary = trim($file_record);

        /* Si una palabra de una cadena generada a partir del descifrado Cesar esta en
        el archivo de texto de diccionario, es una palabra entendible por el humano, por lo tanto,
        se la agrega a un string */
        if (strcmp($word_dictionary, $word) == 0) {
          // echo "Palabra encontrada en el archivo de texto de diccionario: '" . $word_dictionary . "'<br />";
          $decrypted_message .= $word;
        }

      }

      /* Luego de recorrer el archivo de texto de diccionario, se debe reposicionar el puntero
      de este archivo al principio del mismo para comprobar si las palabras restantes estan en
      el diccionario subyacente */
      if (!rewind($dictionary_file)) {
        echo "Hubo un error al reposicionar el puntero del archivo de texto de diccionario.";
      }

    }

    fclose($dictionary_file);
    return $decrypted_message;
  }

  /**
  * Realiza el descifrado Cesar del mensaje de entrada encriptado
  *
  * @param array $alphabet
  * @param integer $offset clave (desplazamiento)
  * @param string $message mensaje de entrada encriptado
  * @return string mensaje de entrada desencriptado
  */
  function decode($alphabet, $offset, $message) {
    // Obtiene un arreglo de caracteres pertenecientes al mensaje
    $message_chars_array = getCharsMessageArray($message);

    $alphabet_size = count($alphabet);
    $decrypted_message;
    $index;

    foreach ($message_chars_array as $key => $cipher_char) {
      /* Si el caracter actualmente recorrido del mensaje no es un espacio,
      se calcula el caracter de descifrado */
      if ($cipher_char !== " ") {
        // Obtiene el indice, en el alfabeto, de un caracter cifrado del mensaje
        $index = getIndex($alphabet, $cipher_char);

        /* Calcula el valor de la posicion del caracter de descifrado
        correspondiente al caracter cifrado actualmente recorrido */
        if ($index - $offset < 0) {
          $index = $alphabet_size + ($index - $offset);
        } else {
          $index = ($index - $offset) % $alphabet_size;
        }

        // Agrega el caracter descifrado al resultado
        $decrypted_message .= $alphabet[$index];
      }

      /* Si el caracter cifrado actualmente recorrido es un espacio, se agrega
      un espacio al resultado */
      if ($cipher_char == " ") {
        $decrypted_message .= " ";
      }

    }

    return $decrypted_message;
  }

  /**
  * Obtiene el valor que tiene la posicion de un caracter en el alfabeto
  *
  * @param array $alphabet
  * @param char $char_message un caracter del mensaje de entrada
  * @return integer el valor que tiene la posicion de un caracter del mensaje
  * de entrada en el alfabeto
  */
  function getIndex($alphabet, $char_message) {

    while ($current_char = current($alphabet)) {

      /* Si el caracter de la posicon actual del alfabeto es igual al caracter del mensaje, retorna
      el valor que tiene la posicion del caracter dentro del alfabeto */
      if ($current_char == $char_message) {
        return key($alphabet);
      }

      next($alphabet);
    }

  }

  /**
  * Convierte una cadena de caracteres (string) en un arreglo
  *
  * @param string $message contiene el mensaje de entrada encriptado
  * @return array que contiene todos los caracteres del mensaje de entrada encriptado
  */
  function getCharsMessageArray($message) {
    return str_split($message);
  }

?>
