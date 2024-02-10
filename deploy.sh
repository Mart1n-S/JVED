#!/bin/sh

# Lancer les conteneurs Docker
docker-compose up -d

# Récupérer l'adresse IP du conteneur MailDev en utilisant docker inspect
MAILDEV_IP=$(docker inspect -f '{{range .NetworkSettings.Networks}}{{.IPAddress}}{{end}}' maildev)

# Vérifier si l'adresse IP est vide
if [ -z "$MAILDEV_IP" ]; then
    echo "Erreur: Impossible de récupérer l'adresse IP du conteneur MailDev."
    exit 1
fi

# Ajouter l'adresse IP du conteneur MailDev au fichier de configuration config_mail.php
echo "<?php" > config/config_mail.php
echo "// Adresse IP du conteneur MailDev" >> config/config_mail.php
echo "define('MAILDEV_IP', '$MAILDEV_IP');" >> config/config_mail.php