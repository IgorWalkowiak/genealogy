# Genealogy Helm Chart

Helm chart dla aplikacji Genealogy - aplikacji do zarządzania drzewem genealogicznym.

## Wymagania

- Kubernetes 1.19+
- Helm 3.2.0+
- PV provisioner wspierający ReadWriteOnce (jeśli używasz MySQL)

## Instalacja

### Instalacja z repozytorium OCI

```bash
helm install genealogy oci://registry-1.docker.io/femex/genealogy
```

### Instalacja z lokalnego katalogu

```bash
helm install genealogy ./helm
```

### Instalacja z niestandardowymi wartościami

```bash
helm install genealogy ./helm -f custom-values.yaml
```

## Konfiguracja

Poniżej znajdują się najważniejsze wartości do konfiguracji:

### Podstawowe ustawienia

| Parametr | Opis | Domyślna wartość |
|----------|------|------------------|
| `replicaCount` | Liczba replik aplikacji | `1` |
| `image.repository` | Repozytorium obrazu Docker | `femex/genealogy` |
| `image.tag` | Tag obrazu Docker | `0.0.1-SNAPSHOT` |
| `image.pullPolicy` | Polityka pobierania obrazu | `IfNotPresent` |

### Service

| Parametr | Opis | Domyślna wartość |
|----------|------|------------------|
| `service.type` | Typ serwisu Kubernetes | `ClusterIP` |
| `service.port` | Port serwisu | `80` |

### Ingress

| Parametr | Opis | Domyślna wartość |
|----------|------|------------------|
| `ingress.enabled` | Włącz/wyłącz Ingress | `false` |
| `ingress.className` | Klasa Ingress | `""` |
| `ingress.hosts` | Lista hostów dla Ingress | `[{host: "genealogy.local", paths: [{path: "/", pathType: "Prefix"}]}]` |

### Zmienne środowiskowe aplikacji

| Parametr | Opis | Domyślna wartość |
|----------|------|------------------|
| `env.APP_NAME` | Nazwa aplikacji | `"Genealogy"` |
| `env.APP_ENV` | Środowisko aplikacji | `"production"` |
| `env.APP_DEBUG` | Tryb debug | `"false"` |
| `env.DB_CONNECTION` | Typ bazy danych | `"mysql"` |
| `env.DB_HOST` | Host bazy danych | `"mysql"` |
| `env.DB_DATABASE` | Nazwa bazy danych | `"genealogy"` |

### Sekrety

| Parametr | Opis | Domyślna wartość |
|----------|------|------------------|
| `secrets.APP_KEY` | Klucz aplikacji Laravel | `""` |
| `secrets.DB_USERNAME` | Użytkownik bazy danych | `"genealogy"` |
| `secrets.DB_PASSWORD` | Hasło do bazy danych | `"secret"` |

### MySQL (opcjonalnie)

| Parametr | Opis | Domyślna wartość |
|----------|------|------------------|
| `mysql.enabled` | Włącz/wyłącz MySQL jako dependency | `true` |
| `mysql.auth.database` | Nazwa bazy danych | `"genealogy"` |
| `mysql.auth.username` | Użytkownik bazy danych | `"genealogy"` |
| `mysql.auth.password` | Hasło do bazy danych | `"secret"` |

### Zasoby

| Parametr | Opis | Domyślna wartość |
|----------|------|------------------|
| `resources.limits.cpu` | Limit CPU | `1000m` |
| `resources.limits.memory` | Limit pamięci | `1024Mi` |
| `resources.requests.cpu` | Request CPU | `500m` |
| `resources.requests.memory` | Request pamięci | `512Mi` |

### Autoscaling

| Parametr | Opis | Domyślna wartość |
|----------|------|------------------|
| `autoscaling.enabled` | Włącz/wyłącz HPA | `false` |
| `autoscaling.minReplicas` | Minimalna liczba replik | `1` |
| `autoscaling.maxReplicas` | Maksymalna liczba replik | `10` |
| `autoscaling.targetCPUUtilizationPercentage` | Docelowe użycie CPU | `80` |

## Przykłady użycia

### Przykład 1: Podstawowa instalacja

```bash
helm install genealogy ./helm
```

### Przykład 2: Instalacja z Ingress

Stwórz plik `values-ingress.yaml`:

```yaml
ingress:
  enabled: true
  className: "nginx"
  annotations:
    cert-manager.io/cluster-issuer: "letsencrypt-prod"
  hosts:
    - host: genealogy.example.com
      paths:
        - path: /
          pathType: Prefix
  tls:
    - secretName: genealogy-tls
      hosts:
        - genealogy.example.com
```

Instalacja:

```bash
helm install genealogy ./helm -f values-ingress.yaml
```

### Przykład 3: Instalacja z zewnętrzną bazą danych

Stwórz plik `values-external-db.yaml`:

```yaml
mysql:
  enabled: false

env:
  DB_HOST: "external-mysql.example.com"
  DB_PORT: "3306"
  DB_DATABASE: "genealogy"

secrets:
  DB_USERNAME: "myuser"
  DB_PASSWORD: "mypassword"
  APP_KEY: "base64:your-app-key-here"
```

Instalacja:

```bash
helm install genealogy ./helm -f values-external-db.yaml
```

## Aktualizacja

```bash
helm upgrade genealogy ./helm
```

Lub z nowymi wartościami:

```bash
helm upgrade genealogy ./helm -f new-values.yaml
```

## Odinstalowanie

```bash
helm uninstall genealogy
```

## Testowanie

Sprawdź czy wszystko działa poprawnie:

```bash
# Port forward
kubectl port-forward svc/genealogy 8080:80

# Otwórz w przeglądarce
open http://localhost:8080
```

## Troubleshooting

### Problem: Pod nie startuje

Sprawdź logi:

```bash
kubectl logs -l app.kubernetes.io/name=genealogy
```

### Problem: Brak połączenia z bazą danych

Sprawdź czy MySQL jest uruchomiony:

```bash
kubectl get pods -l app.kubernetes.io/name=mysql
kubectl logs -l app.kubernetes.io/name=mysql
```

### Problem: Brak APP_KEY

Wygeneruj APP_KEY lokalnie:

```bash
# Uruchom kontener Laravel tymczasowo
docker run --rm femex/genealogy:latest php artisan key:generate --show
```

Następnie dodaj wygenerowany klucz do `secrets.APP_KEY` w values.yaml.

## Licencja

MIT

