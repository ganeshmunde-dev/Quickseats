\# QuickSeats



\## Bus Ticket Booking and Reservation System



QuickSeats is a web-based bus ticket booking system built with \*\*PHP, MySQL, JavaScript, HTML, and CSS\*\*.



The platform allows customers to search available bus routes, select seats, complete bookings, receive OTP verification, and view their booking details. It also includes an admin panel for managing routes and monitoring bookings.



\---



\## Features



\### Customer Features



\* Customer registration

\* Email OTP verification

\* Customer login

\* Bus route search

\* Travel date selection

\* Available seat display

\* Seat selection

\* Bus ticket booking

\* UPI / QR payment flow

\* Booking confirmation

\* Booking receipt/details

\* My Bookings section

\* Booking history



\### Admin Features



\* Admin authentication

\* Admin dashboard

\* View bookings

\* View available routes

\* Add bus routes

\* Delete bus routes

\* Manage route information

\* Monitor booking data



\### Additional Features



\* Responsive design

\* REST-style PHP APIs

\* MySQL database integration

\* Secure prepared SQL statements

\* Transaction-based booking process

\* Email OTP using PHPMailer

\* Dynamic seat availability

\* UPI payment integration flow



\---



\## Technology Stack



\### Frontend



\* HTML5

\* CSS3

\* JavaScript

\* Fetch API

\* Responsive Web Design



\### Backend



\* PHP

\* MySQL

\* MySQLi

\* REST-style APIs

\* PHPMailer



\### Database



\* MySQL

\* Prepared statements

\* Database transactions



\---



\## System Architecture



```text

Customer

&#x20;  |

&#x20;  v

QuickSeats Frontend

HTML + CSS + JavaScript

&#x20;  |

&#x20;  v

PHP API Layer

&#x20;  |

&#x20;  +-------------------+

&#x20;  |                   |

&#x20;  v                   v

MySQL Database     PHPMailer

&#x20;  |                   |

&#x20;  v                   v

Bookings, Routes    Email OTP

Customers, Admin

```



\---



\## Main Pages



```text

Home

Login

Registration

Booking

My Bookings

Booking Success

UPI Payment

Admin Dashboard

```



\---



\## Booking Flow



```text

Search Bus

&#x20;   |

&#x20;   v

Select Route

&#x20;   |

&#x20;   v

Select Seats

&#x20;   |

&#x20;   v

Enter Customer Details

&#x20;   |

&#x20;   v

Choose Payment Method

&#x20;   |

&#x20;   v

UPI / QR Payment

&#x20;   |

&#x20;   v

Confirm Booking

&#x20;   |

&#x20;   v

Generate Booking Details

&#x20;   |

&#x20;   v

My Bookings

```



\---



\## OTP Verification



QuickSeats uses \*\*PHPMailer\*\* to send verification codes through email.



The authentication flow is:



```text

Enter Email

&#x20;   |

&#x20;   v

Generate OTP

&#x20;   |

&#x20;   v

Send OTP via Email

&#x20;   |

&#x20;   v

Enter OTP

&#x20;   |

&#x20;   v

Verify OTP

&#x20;   |

&#x20;   v

Continue Registration/Login

```



\---



\## Seat Management



The system provides dynamic seat availability for bus routes.



During booking, the application checks already booked seats before confirming a reservation.



The booking process uses database transactions to help prevent inconsistent seat counts when a booking is processed.



\---



\## UPI Payment



QuickSeats includes a UPI-based payment flow.



Customers can:



\* Select UPI as the payment method

\* Generate a UPI payment link

\* View a QR code

\* Open supported UPI applications

\* Copy the UPI payment link when required



The project demonstrates the payment workflow without exposing private payment credentials.



\---



\## API Structure



The backend is organized into PHP API endpoints.



Examples include:



```text

api/

|

+-- login.php

+-- register\_customer.php

+-- send\_otp.php

+-- verify\_otp.php

+-- routes.php

+-- get\_booked\_seats.php

+-- book\_ticket.php

+-- get\_bookings.php

+-- get\_single\_booking.php

+-- my\_bookings.php

+-- admin\_auth.php

+-- check\_customer.php

```



The frontend communicates with these endpoints using JavaScript `fetch()` requests.



\---



\## Database



QuickSeats uses MySQL for storing application data.



Main database entities include:



```text

customer\_register

&#x20;       |

&#x20;       +-- Customer information



bus\_routes

&#x20;       |

&#x20;       +-- Bus and route information



bookmy\_bus

&#x20;       |

&#x20;       +-- Booking information



admin\_accounts

&#x20;       |

&#x20;       +-- Admin authentication

```



The project includes a clean database schema for setting up the application.



\---



\## Security Practices



The project uses several backend security practices, including:



\* Prepared SQL statements

\* MySQLi parameter binding

\* Password hashing

\* Database transactions

\* Input validation

\* Separate local configuration files

\* Git-ignored private configuration

\* Protected local database credentials



Private credentials and local configuration files should be configured separately when deploying the project.



\---



\## Installation



\### Requirements



\* XAMPP

\* Apache

\* MySQL

\* PHP

\* Modern web browser



\### Setup



Clone the repository:



```bash

git clone https://github.com/ganeshmunde-dev/Quickseats.git

```



Move the project into the XAMPP `htdocs` directory.



Example:



```text

C:\\xampp\\htdocs\\projects\\quickseats

```



Start \*\*Apache\*\* and \*\*MySQL\*\* from XAMPP.



Create a MySQL database named:



```text

support

```



Import the provided database schema from:



```text

database/schema.sql

```



Configure the local database connection in the local configuration file.



Then open the project through your local Apache server.



Example:



```text

http://localhost/projects/quickseats/

```



\---



\## Project Structure



```text

quickseats/

|

+-- api/

|   +-- Authentication APIs

|   +-- Booking APIs

|   +-- Route APIs

|   +-- Admin APIs

|

+-- assets/

|   +-- css/

|   +-- js/

|   +-- images/

|

+-- database/

|   +-- schema.sql

|

+-- includes/

|   +-- Database configuration

|   +-- Application configuration

|

+-- src/

|   +-- PHPMailer

|

+-- index.html

+-- home.html

+-- login.html

+-- booking.html

+-- my\_bookings.html

+-- success.html

+-- admin.html

+-- upi\_redirect.html

```



\---



\## Key Highlights



\* Complete bus booking workflow

\* Dynamic seat availability

\* Customer authentication

\* Email OTP verification

\* Admin management system

\* MySQL database integration

\* PHP backend APIs

\* UPI / QR payment workflow

\* Booking history

\* Responsive interface

\* Transaction-based booking logic



\---



\## Learning Outcomes



This project provided practical experience with:



\* PHP backend development

\* MySQL database design

\* MySQLi

\* REST API development

\* JavaScript Fetch API

\* Authentication systems

\* OTP verification

\* Email integration

\* Database transactions

\* Seat management logic

\* Booking workflows

\* Responsive web development

\* Admin dashboard development



\---



\## Future Improvements



Possible future improvements include:



\* Real payment gateway integration

\* Online ticket cancellation

\* Refund management

\* SMS notifications

\* PDF ticket generation

\* Advanced bus seat layouts

\* User profile management

\* Bus operator management

\* Booking analytics

\* Cloud deployment



\---



\## Author



\*\*Ganesh Munde\*\*



Frontend Developer | Java Full Stack Developer



GitHub:



```text

https://github.com/ganeshmunde-dev

```



\---



\## Repository



QuickSeats source code:



```text

https://github.com/ganeshmunde-dev/Quickseats

```



\---



\## License



This project is developed for educational, portfolio, and demonstration purposes.



