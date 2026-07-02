# Sistema de reservaciones Nuve Hoteles

Este proyecto es una aplicacion Laravel 12 + Vue 3/Inertia para consultar disponibilidad hotelera, crear reservaciones, cobrar con Stripe, registrar reservas con pago en recepcion, enviar metricas a Enzo Marketing y administrar reservas desde un dashboard.

Hoteles configurados actualmente:

- `torreon`: Nuve Torreon.
- `gomez`: Nuve Gomez.
- `parras`: Nuve Parras.

La configuracion por hotel vive en `config/services.php`, dentro de `services.hotels`. Cada hotel puede tener sus propias credenciales de FC Sistemas, Stripe y correo administrativo.

## Stack tecnico

- Backend: Laravel 12, PHP 8.2, Eloquent, Sanctum.
- Frontend: Vue 3, Inertia, Vite, Element Plus, Tailwind.
- Pagos: Stripe Checkout con captura manual.
- Correos: Resend/Laravel Mail.
- Proveedor hotelero: FC Sistemas por SOAP/XML.
- Metricas externas: Enzo Marketing por HTTP JSON/form payload.

## Archivos principales

- `routes/web.php`: paginas publicas, disponibilidad, checkout, webhook Stripe y login del dashboard.
- `routes/api.php`: endpoints JSON del dashboard, catalogos SOAP de direccion y acciones de reservacion.
- `routes/console.php`: agenda el envio diario de metricas a Enzo.
- `config/services.php`: credenciales y parametros de FC Sistemas, Stripe, Enzo y hoteles.
- `app/Http/Controllers/ReservaController.php`: busqueda de disponibilidad y validacion de cupon.
- `app/Http/Controllers/CheckoutController.php`: creacion de reserva, hold SOAP, Stripe Checkout, pago en recepcion y estado de checkout.
- `app/Http/Controllers/StripeWebhookController.php`: confirmacion definitiva del pago y conciliacion con FC Sistemas.
- `app/Http/Controllers/DashboardController.php`: datos, filtros, login y acciones del dashboard.
- `app/Http/Controllers/ApiController.php`: catalogos SOAP para estados, ciudades y codigo postal.
- `app/Services/PricingService.php`: fuente de verdad para promociones, cupones y total final.
- `app/Services/GlobalPromotionService.php`: seleccion de promociones automaticas.
- `app/Services/CuponService.php`: validacion, calculo y consumo de cupones.
- `app/Services/HotelConfig.php`: normalizacion de hoteles y acceso a configuracion por hotel.
- `app/Console/Commands/SendMonthlyReservationsDaily.php`: envio diario de metricas mensuales a Enzo.
- `app/Models/Reservation.php`: modelo central de reservaciones.

## Instalacion local

```bash
composer install
npm install
php artisan key:generate
php artisan migrate
npm run build
```

Este checkout no trae `.env.example`. Para una instalacion nueva, crea `.env` tomando como referencia la seccion "Variables de entorno importantes" de este README y los nombres usados en `config/services.php`.

Para desarrollo:

```bash
composer run dev
```

Ese comando levanta servidor Laravel, Vite, cola y logs segun `composer.json`.

Comandos utiles:

```bash
php artisan migrate
php artisan db:seed
php artisan config:clear
php artisan route:clear
php artisan test
npm run build
```

Nota: `DatabaseSeeder` carga cupones y promociones de ejemplo. `RolSeeder` existe, pero no esta incluido en `DatabaseSeeder`; si necesitas sembrar roles (`usuario`, `admin`, `superadmin`), ejecutalo de forma explicita o agregalo al seeder principal segun el ambiente.

## Variables de entorno importantes

No subas valores reales de secretos al repositorio. El README solo lista nombres.

Aplicacion y base:

- `APP_URL`
- `APP_ENV`
- `APP_DEBUG`
- `DB_CONNECTION`
- `DB_HOST`
- `DB_PORT`
- `DB_DATABASE`
- `DB_USERNAME`
- `DB_PASSWORD`
- `SESSION_DRIVER`
- `SANCTUM_STATEFUL_DOMAINS`
- `SESSION_DOMAIN`
- `SESSION_SECURE_COOKIE`
- `SESSION_SAME_SITE`

FC Sistemas SOAP:

- `FC_SOAP_ENDPOINT`
- `FC_SOAP_PASS`
- `FC_SOAP_CX`
- `FC_SOAP_CX_GOMEZ`
- `FC_SOAP_CX_PARRAS`
- `FC_SOAP_DEBUG`
- `FC_SOAP_TIMEOUT`
- `FC_AVAILABILITY_SOAP_TIMEOUT`
- `FC_PARRAS_AVAILABILITY_SOAP_TIMEOUT`
- `FC_AVAILABILITY_REQUEST_TIME_LIMIT`
- `FC_HOLD_TTL_MINUTES`
- `FC_DUMMY_CC`

Stripe:

- `STRIPE_SECRET_KEY`
- `STRIPE_PUBLIC_KEY`
- `STRIPE_WEBHOOK_SECRET`
- `STRIPE_TORREON_SECRET_KEY`
- `STRIPE_TORREON_PUBLIC_KEY`
- `STRIPE_TORREON_WEBHOOK_SECRET`
- `STRIPE_GOMEZ_SECRET_KEY`
- `STRIPE_GOMEZ_PUBLIC_KEY`
- `STRIPE_GOMEZ_WEBHOOK_SECRET`
- `STRIPE_PARRAS_SECRET_KEY`
- `STRIPE_PARRAS_PUBLIC_KEY`
- `STRIPE_PARRAS_WEBHOOK_SECRET`

Si no existen claves por hotel, `config/services.php` usa fallback a las claves globales de Stripe.

Enzo:

- `ENZO_API_KEY`
- `ENZO_METRICS_URL`
- `ENZO_TORREON_POST_ID`
- `ENZO_GOMEZ_POST_ID`
- `ENZO_PARRAS_POST_ID`

Correos administrativos:

- `HOTEL_TORREON_BOOKING_RECEPTION_ADMIN_TO`
- `HOTEL_GOMEZ_BOOKING_RECEPTION_ADMIN_TO`
- `HOTEL_PARRAS_BOOKING_RECEPTION_ADMIN_TO`
- `RESEND_API_KEY`

## Shopify

Este repositorio no tiene integracion Shopify actualmente.

No hay referencias a `Shopify`, paquetes de Shopify, controladores Shopify ni variables Shopify en el codigo actual. El flujo de pago implementado es Stripe Checkout. Si alguien menciona "Shopify" al administrar esta app, debe confirmarse si realmente se refiere a Stripe o si hace falta desarrollar una integracion nueva.

## Flujo publico de reservacion

El usuario puede entrar por:

- `/`: home general.
- `/disponibilidad`: disponibilidad general para Torreon/Gomez.
- `/parras/reservar`: buscador dedicado de Parras.
- `/parras/disponibilidad`: disponibilidad dedicada de Parras.

Flujo normal:

1. El usuario selecciona fechas, habitaciones y adultos.
2. El frontend envia el formulario a `ReservaController`.
3. `ReservaController` valida fechas y arma la consulta SOAP a FC Sistemas.
4. La respuesta SOAP se parsea y se convierte a grupos de habitaciones por hotel.
5. La pagina `resources/js/Pages/Disponibilidad.vue` muestra habitaciones, imagenes, tarifas, desglose, promocion automatica y cupon.
6. El usuario elige `Pagar en linea` o `Pagar en recepcion`.

La pantalla de disponibilidad muestra un contador de sesion de 5 minutos. Al terminar, recarga la pagina para evitar operar sobre tarifas viejas.

## Disponibilidad y tarifas SOAP

La consulta principal de disponibilidad esta en `ReservaController::callSoapTarifasFechas()`.

Metodo SOAP usado:

- `GetHabitacionTarifasFechasFotoDescrip_ES_EN`

Datos enviados:

- `lFechaIni`: fecha de entrada con `T00:00:00`.
- `lFechaFinal`: fecha de salida con `T00:00:00`.
- `lAdul`: adultos.
- `lMen`: siempre `0`.
- `lJr`: siempre `0`.
- `lHabs`: numero de habitaciones.
- `lPassCliente`: password FC del hotel.
- `lStringCxSAHM`: cadena de conexion FC del hotel.

Configuracion usada:

- `HotelConfig::fc($hotelCode)`
- `services.hotels.{hotel}.fc.soap_endpoint`
- `services.hotels.{hotel}.fc.pass`
- `services.hotels.{hotel}.fc.cx`
- `services.hotels.{hotel}.fc.availability_soap_timeout`

Parseo:

- `ReservaController::parseTarifasAll()` lee los nodos `cTarifaHabitacionFotoFechaDescrip_ES_EN`.
- Extrae codigo de habitacion, nombre, plan, imagenes, tarifas por fecha, total de noches y suma en centavos.
- El frontend multiplica el total de noches por el numero de habitaciones para mostrar el subtotal.

Si falla SOAP, la pagina muestra un mensaje generico de error y se registra un warning en logs con `hotel_code` y mensaje.

## Catalogos de direccion SOAP

`ApiController` expone endpoints auxiliares:

- `GET /api/get-states`
- `POST /api/get-cities`
- `POST /api/get-address`

Metodos SOAP usados:

- `GetPaisRegiones`: estados/regiones de Mexico.
- `GetPaisRegionCiudades`: ciudades por region.
- `GetPaisRegionCiudadCP`: region y ciudad por codigo postal.

Estos endpoints se usan para completar datos del cliente. Actualmente el controlador usa el endpoint base de FC Sistemas y `FC_SOAP_CX`.

## Precios, promociones y cupones

La fuente de verdad del precio final es backend, no el frontend.

`PricingService::buildPricingData()` recalcula antes de:

- Crear checkout Stripe.
- Crear reserva de pago en recepcion.
- Guardar `amount_cents`.
- Guardar desglose en `Reservation.meta`.

Orden de calculo:

1. Subtotal original.
2. Promocion automatica.
3. Cupon manual sobre el subtotal ya descontado.
4. Total final.

Ejemplo: si el subtotal es `$1,000`, una promocion de 15% deja `$850`; un cupon de 10% se aplica sobre `$850`, dejando `$765`.

Promociones automaticas:

- Tabla: `global_promotions`.
- Servicio: `GlobalPromotionService`.
- Campos de control:
  - `status`
  - `discount_type`: `percentage` o `fixed`.
  - `discount_value`
  - `booking_starts_at` / `booking_ends_at`: ventana en la que la reserva puede hacerse.
  - `stay_starts_at` / `stay_ends_at`: fechas de hospedaje que califican.
  - `hotel_code`: `null` aplica global; si tiene valor, solo aplica a ese hotel.
  - `room_type_code`: `null` aplica global; si tiene valor, solo aplica a ese tipo.
  - `priority`: gana la promocion con mayor prioridad; en empate gana el mayor `id`.

Cupones:

- Tabla: `cupon_codes`.
- Servicio: `CuponService`.
- El codigo se normaliza con `strtoupper(trim())`.
- Valida existencia, `status`, fechas `starts_at`/`expires_at` y limite de uso.
- `discount_type` puede ser `percentage` o `fixed`.
- `consumeCoupon()` incrementa `times_used` dentro de transaccion con `lockForUpdate()`.

Cuándo se consume un cupon:

- Pago en linea: solo despues de pago confirmado, captura Stripe exitosa y confirmacion con FC Sistemas en el webhook.
- Pago en recepcion: inmediatamente despues de crear la reserva local.

## Pago en linea con Stripe

Ruta:

- `POST /checkout`
- Controlador: `CheckoutController::create()`

Flujo:

1. Valida hotel, habitacion, fechas, monto, moneda, adultos, habitaciones y cupon.
2. Normaliza `hotel_code` con `HotelConfig::normalize()`.
3. Obtiene datos del cliente desde usuario autenticado o `userInfo`.
4. Recalcula precio con `PricingService`.
5. Crea un hold temporal en FC Sistemas con `fInsertaReservaNew`.
6. Guarda una fila en `reservations` con estado `awaiting_payment`.
7. Crea una sesion de Stripe Checkout con `capture_method = manual`.
8. Usa `expires_at` igual al vencimiento del hold del proveedor.
9. Guarda `stripe_session_id` y `stripe_checkout_url`.
10. Devuelve la URL de Stripe al frontend.

Datos importantes de la reserva:

- `provider_folio`: folio devuelto por FC Sistemas.
- `provider_hold_expires_at`: vencimiento del hold.
- `status`: inicia como `awaiting_payment`.
- `amount_cents`: total final en centavos.
- `meta`: desglose de promociones/cupones y datos de idempotencia.
- `origin_page`: viene del header `referer` o de la URL actual.

## Hold SOAP en FC Sistemas

`CheckoutController::createProviderHold()` llama:

- `fInsertaReservaNew`

Datos principales enviados:

- Tipo de habitacion.
- Check-in/check-out.
- Habitaciones.
- Adultos.
- Tarifa por noche calculada como total / noches / habitaciones.
- Datos del cliente.
- Tarifa configurada (`rate_name`, por defecto `WWW_CA`).
- Numero de tarjeta dummy (`FC_DUMMY_CC`).
- Fecha limite del hold.

Si el hold falla, no se crea checkout Stripe y el endpoint responde error `502`.

## Confirmacion definitiva por webhook Stripe

Ruta:

- `POST /checkout/stripe/webhook/{hotel?}`
- Controlador: `StripeWebhookController::handle()`

El webhook es la fuente de verdad para finalizar el pago. La pagina de exito no debe marcar una reserva como pagada por si sola.

Evento principal:

- `checkout.session.completed`

Flujo del webhook:

1. Valida firma con el webhook secret del hotel.
2. Busca la reserva por `reservation_id` en metadata.
3. Usa transaccion y `lockForUpdate()` para evitar dobles procesos.
4. Guarda `event.id` en `meta.stripe_event_ids` para idempotencia.
5. Si la reserva ya esta final (`paid`, `failed`, `expired`, `cancelled`), no reprocesa.
6. Si el hold vencio, marca `expired`.
7. Consulta disponibilidad real en FC Sistemas con `fDisponibilidadTipo`.
8. Si no hay disponibilidad, cancela el PaymentIntent si estaba en `requires_capture`, marca `failed` y guarda `fail_reason = no_availability`.
9. Si hay disponibilidad, captura el PaymentIntent manualmente.
10. Confirma pago con FC Sistemas usando `fPagoConfirmado`.
11. Cambia status de proveedor a vigente con `fCambioStatusReserva`.
12. Si falla la confirmacion con proveedor, intenta refund y marca `failed` con `fail_reason = provider_failed_refunded`.
13. Consume cupon si aplica.
14. Marca la reserva como `paid`.
15. Despues del commit, envia correo al cliente y al admin.

Eventos adicionales:

- `checkout.session.expired`: cambia `awaiting_payment` a `expired`.
- `payment_intent.payment_failed`: cambia `awaiting_payment` a `failed`.
- `payment_intent.canceled`: cambia `awaiting_payment` a `cancelled`.

Si ocurre un error temporal real dentro del webhook, responde `500` para que Stripe reintente.

## Pagina de exito y estado de checkout

Rutas:

- `GET /checkout/success`
- `GET /checkout/status`

`CheckoutController::success()` renderiza `Checkout/Thanks.vue` con datos iniciales. Esa vista consulta `/checkout/status?session_id=...` cada 2.5 segundos hasta que la reserva este en estado final.

Estados finales:

- `paid`
- `failed`
- `expired`
- `cancelled`

La vista solo muestra datos de la reserva cuando el estado es `paid`. En estados fallidos no muestra confirmacion de reserva.

## Pago en recepcion

Ruta:

- `POST /create-booking-reception`
- Controlador: `CheckoutController::bookingInReception()`

Flujo:

1. Valida hotel, habitacion, fechas, adultos, habitaciones, monto, cupon y datos del cliente.
2. Recalcula precio con `PricingService`.
3. Crea una reserva local con:
   - `status = booking_in_reception`
   - `provider_folio = RECEPCION-{uniqid}`
   - `provider_hold_expires_at = now() + 2 horas`
4. Consume cupon inmediatamente si aplica.
5. Envia correo al cliente.
6. Envia correo al admin configurado en `services.hotels.{hotel}.mail.booking_in_reception_admin_to`.
7. Devuelve `201`.

Importante: este flujo no crea hold SOAP con FC Sistemas y no pasa por Stripe.

## Estados de reservacion

Campo principal: `reservations.status`.

Valores:

- `pending`: creado sin hold, casi no se usa en el flujo actual.
- `awaiting_payment`: hold creado, esperando pago Stripe.
- `paid`: pagada y confirmada con proveedor.
- `expired`: vencio el hold o expiro checkout.
- `cancelled`: cancelada.
- `failed`: fallo pago, disponibilidad o proveedor.
- `booking_in_reception`: reserva local para pago en recepcion.

Campo administrativo: `reservations.is_confirmed`.

Constantes en `Reservation`:

- `CONFIRMATION_PENDING = 0`
- `CONFIRMATION_CONFIRMED = 1`
- `CONFIRMATION_CANCELLED = 2`

Este campo se usa especialmente para clasificar reservas de recepcion en dashboard y metricas. Una reserva puede tener `status = booking_in_reception` y estar pendiente, confirmada o cancelada administrativamente por `is_confirmed`.

## Dashboard administrativo

Login:

- `POST /login-dashboard`
- Controlador: `DashboardController::loginDashboard()`

Logout:

- `POST /logout-dashboard`

APIs protegidas:

- `GET /api/dashboard-data`
- `GET /api/paginate-reservations`
- `POST /api/reservation/add-description`
- `PATCH /api/reservation/change-is-confirmed`
- `PATCH /api/reservation/mark-as-cancelled`

Roles:

- `rol_id = 1`: usuario normal, sin acceso dashboard.
- `rol_id = 2`: admin de hotel, requiere `users.hotel_code`.
- `rol_id = 3`: superadmin, puede consultar todos los hoteles.

Alcance por hotel:

- `DashboardController::resolveDashboardHotelScope()` determina hoteles permitidos.
- Superadmin puede pedir `hotel_code`.
- Admin normal queda limitado a su `hotel_code` asignado.

Filtros de fecha:

- `start_date`
- `end_date`

Se aplican del lado servidor por `created_at`. El backend devuelve un objeto `filters` normalizado para que el frontend sincronice estado.

## Semantica de metricas del dashboard

`DashboardController` tiene dos reglas principales:

`whereDashboardSale()`:

- Incluye `status in ('paid', 'booking_in_reception')`.
- Excluye reservas con `is_confirmed = 2`.
- Se usa para ventas diarias, semanales, mensuales y grafica por mes.

`whereDashboardTotal()`:

- Incluye `paid`.
- Incluye `booking_in_reception`.
- Incluye canceladas administrativas con `is_confirmed = 2`.
- Se usa para `total_amount_cents` e `total_reservations`.

Campos devueltos por `/api/dashboard-data`:

- `allowed_hotel_codes`
- `selected_hotel_code`
- `total_cents_per_month`
- `total_amount_cents`
- `total_amount_cents_daily`
- `total_amount_cents_weekly`
- `total_amount_cents_monthly`
- `total_reservations`
- `total_reservations_weekly`
- `reservations_weekly`
- `user_reservations`
- `filters`

La lista de reservas del dashboard trae solo `paid` y `booking_in_reception`, con datos de usuario o invitado via `COALESCE`.

## Acciones administrativas sobre reservas

Agregar descripcion:

- `POST /api/reservation/add-description`
- Requiere `id` y `description`.
- Actualiza `reservations.description`.

Cambiar confirmacion:

- `PATCH /api/reservation/change-is-confirmed`
- Requiere `id` e `is_confirmed` booleano.
- Guarda `0` o `1`.

Marcar cancelada:

- `PATCH /api/reservation/mark-as-cancelled`
- Requiere `id`.
- Guarda `is_confirmed = Reservation::CONFIRMATION_CANCELLED` (`2`).

Si se agregan columnas nuevas a reservas, revisar ambos lados:

- Escritura en `CheckoutController`.
- Proyecciones/selects de `DashboardController`, porque usa listas explicitas de columnas.

## Envio de metricas a Enzo

Comando:

```bash
php artisan app:send-monthly-reservations-daily
```

Agenda:

- `routes/console.php`
- Diario a las `03:00`.

Controlador/comando:

- `app/Console/Commands/SendMonthlyReservationsDaily.php`

Hoteles enviados:

- `torreon`
- `gomez`
- `parras`

Configuracion:

- `services.enzo.api_key`
- `services.enzo.metrics_url`
- `services.enzo.post_ids`

Payload enviado:

- `api_key`
- `post_id`
- `date`
- `metric_count`
- `metric_revenue`
- `pending_reservation_count`
- `pending_reservation_revenue`
- `canceled_reservation_count`
- `canceled_reservation_revenue`
- `source`

Reglas de conteo:

- Base mensual: `created_at` del mes y anio actuales.
- Siempre filtrado por `hotel_code`.
- `metric_count` y `metric_revenue` usan `whereMetricTotal()`:
  - `status = paid`
  - `status = booking_in_reception` no cancelada por `is_confirmed`
  - o `is_confirmed = 2`
- Pendientes:
  - `status = booking_in_reception`
  - no canceladas por `is_confirmed`
- Canceladas:
  - `is_confirmed = 2`

Auditoria local:

- Tabla: `send_data_to_enzos`.
- Modelo: `SendDataToEnzo`.
- Guarda hotel, conteos, montos en centavos, payload, respuesta, status HTTP y `post_id`.

No ejecutes este comando en produccion para pruebas si no quieres enviar datos reales a Enzo. Para validar cambios de codigo, primero usa `php -l` o pruebas locales.

## Correos

Mails principales:

- `ReservationConfirmedMail`: confirmacion al cliente por reserva pagada.
- `ReservationConfirmedAdminMail`: aviso admin por reserva pagada.
- `BookingInReceptionCustomerMail`: aviso cliente por pago en recepcion.
- `BookingInReceptionAdminMail`: aviso admin por pago en recepcion.

Vistas:

- `resources/views/emails/reservations/confirmed.blade.php`
- `resources/views/emails/reservations/confirmed_admin.blade.php`
- `resources/views/emails/reservations/booking_in_reception_customer.blade.php`
- `resources/views/emails/reservations/booking_in_reception_admin.blade.php`

En pagos Stripe, el envio se hace despues del commit del webhook. Si falla el correo, se registra error, pero no se revierte la reserva pagada.

## Tablas principales

`reservations`:

- Reserva central.
- Guarda huesped, usuario, hotel, habitacion, fechas, monto, folio proveedor, estado, Stripe, `origin_page` y `meta`.

`cupon_codes`:

- Codigos de descuento manuales.
- Controla tipo, valor, limite, usos, estado y vigencia.

`global_promotions`:

- Promociones automaticas.
- Controla ventana de reserva, ventana de hospedaje, hotel, habitacion y prioridad.

`send_data_to_enzos`:

- Auditoria de envios a Enzo.

`users`:

- Usuarios cliente/admin.
- `rol_id` define permisos.
- `hotel_code` limita administradores de hotel.

## Rutas publicas principales

- `GET /`
- `GET /quienes-somos`
- `GET /nuestros-hoteles`
- `GET /experiencias`
- `GET /disponibilidad`
- `POST /disponibilidad`
- `POST /disponibilidad/cupon/validate`
- `GET /parras`
- `GET /parras/reservar`
- `GET /parras/disponibilidad`
- `POST /parras/disponibilidad`
- `POST /checkout`
- `POST /checkout/stripe/webhook/{hotel?}`
- `GET /checkout/success`
- `GET /checkout/cancel`
- `GET /checkout/success/reception`
- `GET /checkout/status`
- `POST /create-booking-reception`

## Checklist para administrar correctamente

Despues de cambiar `.env`:

```bash
php artisan config:clear
php artisan route:clear
```

Despues de cambiar frontend:

```bash
npm run build
```

Despues de cambiar PHP:

```bash
php -l app/Http/Controllers/CheckoutController.php
php -l app/Http/Controllers/StripeWebhookController.php
php -l app/Http/Controllers/ReservaController.php
php -l app/Http/Controllers/DashboardController.php
```

Despues de cambiar migraciones:

```bash
php artisan migrate
```

Para confirmar rutas:

```bash
php artisan route:list
```

Para revisar metricas Enzo sin asumir:

1. Revisar `SendMonthlyReservationsDaily.php`.
2. Confirmar mes actual con `now()`.
3. Consultar `reservations` por `created_at`, `hotel_code`, `status` e `is_confirmed`.
4. Revisar la fila nueva en `send_data_to_enzos` despues de ejecutar el comando.

Para depurar SOAP:

1. Verificar `FC_SOAP_PASS` y cadena `FC_SOAP_CX` del hotel.
2. Activar temporalmente `FC_SOAP_DEBUG=true`.
3. Revisar `storage/logs/laravel.log`.
4. Revisar `storage/logs/soap-debug.log` para eventos de hold, pago y cambio de status.
5. Volver a desactivar debug si expone datos sensibles.

Para depurar Stripe:

1. Confirmar que la ruta del webhook incluya el hotel correcto si usas secretos por hotel.
2. Revisar `stripe_session_id` y `stripe_payment_intent_id` en `reservations`.
3. Revisar `Reservation.meta.stripe_event_ids` para idempotencia.
4. Revisar `fail_reason` en `meta` si la reserva quedo `failed`.

## Reglas para cambios futuros

- No confiar en montos enviados por frontend; siempre recalcular con `PricingService`.
- No marcar `paid` desde la pagina de exito; solo el webhook debe finalizar.
- Si se cambia una metrica del dashboard, revisar tambien el comando de Enzo si debe coincidir.
- Si se agrega un hotel, actualizar `config/services.php`, variables `.env`, `ENZO_*_POST_ID`, Stripe, FC Sistemas y correos admin.
- Si se agrega un campo de reserva, actualizar modelo, migracion, ambos flujos de creacion y selects del dashboard.
- Si se cambia la semantica de cancelacion, revisar `Reservation::CONFIRMATION_CANCELLED`, dashboard y Enzo juntos.
