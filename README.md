Cel zadania
Celem jest stworzenie prostej aplikacji webowej, która pokazuje aktualną pogodę dla miast,
w których znajdują się klienci firmy. Dzięki temu handlowcy mogą się przygotować do
rozmowy z klientem, np. sprawdzając pogodę w jego lokalizacji.


Jak uruchomić aplikację:

-Frontend:

(komendy w konsoli)
cd frontend
npm install
npm run dev

-Backend:

Należy utworzyć bazę danych mysql o nazwie weather_app
(komendy w konsoli)
cd backend
php artisan migrate
php artisan seed
php artisan serve
