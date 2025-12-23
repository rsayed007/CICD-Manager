# CICD Manager

## Installation

### 1️⃣ Build the Project

Build and start all Docker containers:

```bash
docker compose up -d --build
````

---

### 2️⃣ Change Nginx Password (Optional)

To set or update the Nginx HTTP basic auth password:

```bash
docker run --rm httpd:alpine htpasswd -nbB <username> <password> > ./docker/nginx/.htpasswd
```

> Replace `<username>` and `<password>` with your desired credentials.

After updating the password, restart the Nginx container:

```bash
docker compose restart nginx
```

---

### 3️⃣ Enter the Application Container

After building the project, enter the app container for initial setup:

```bash
docker compose exec app bash
```

Inside the container, you can run regular Laravel commands, for example:

```bash
php artisan migrate
php artisan db:seed
php artisan config:cache
```

Exit the container anytime by typing:

```bash
exit
```

---

### 4️⃣ Troubleshooting

#### Permission Denied Error

If you see a permission error when running Docker commands, add your user to the `docker` group:

```bash
sudo usermod -aG docker $USER
newgrp docker
```

Then try again:

```bash
docker compose up -d --build
```

#### Notes

* Use `docker compose` (v2 syntax) instead of `docker-compose` (deprecated).
* Always restart Nginx after changing the `.htpasswd` file.
* Commands inside the container should be run as needed for Laravel setup.
