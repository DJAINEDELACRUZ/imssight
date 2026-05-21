# Flujo de trabajo IMSSight

## Desarrollo local (MacBook)

Entrar al proyecto:

```bash
cd ~/projects/imssight
```

Abrir Visual Studio Code:

```bash
code .
```

Enviar cambios:

```bash
git add .
git commit -m "descripcion"
git push
```

---

## Servidor Ubuntu

Entrar al servidor:

```bash
ssh usuario@IP_DEL_SERVIDOR
```

Entrar al proyecto:

```bash
cd ~/projects/imssight
```

Actualizar cambios:

```bash
git pull
```

---

## Rebuild Docker (solo si cambia infraestructura)

Ejecutar únicamente si cambia:

- Dockerfile
- docker-compose.yml
- extensiones PHP
- paquetes
- versiones

```bash
docker compose up -d --build
```