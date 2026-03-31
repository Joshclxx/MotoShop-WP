<?php
/**
 * Philippine geographic data for the checkout address cascade.
 *
 * Format: [ province-slug => [ 'City Name' => ['Barangay 1', 'Barangay 2', ...] ] ]
 *
 * This file contains the most common serviceable areas. Expand as delivery
 * coverage grows. Source: PSGC (Philippine Standard Geographic Code).
 *
 * @package SwiftCart
 * @since   1.0.0
 */

declare( strict_types=1 );

defined( 'ABSPATH' ) || exit;

return array(

	// ── Metro Manila ──────────────────────────────────────────────────────────
	'metro-manila' => array(
		'Caloocan'          => array( 'Bagong Silang', 'Camarin', 'Deparo', 'Grace Park East', 'Grace Park West', 'Kaunlaran', 'Maypajo', 'Sangandaan', 'Tala', 'Tonsuya' ),
		'Las Piñas'         => array( 'Almanza Uno', 'Almanza Dos', 'BF Resort', 'Pamplona Uno', 'Pamplona Dos', 'Pamplona Tres', 'Pilar', 'Pulang Lupa Uno', 'Pulang Lupa Dos', 'Talon Singko' ),
		'Makati'            => array( 'Bangkal', 'Bel-Air', 'Comembo', 'East Rembo', 'Forbes Park', 'Guadalupe Nuevo', 'Guadalupe Viejo', 'Poblacion', 'Rockwell', 'San Antonio', 'San Lorenzo', 'Urdaneta' ),
		'Malabon'           => array( 'Acacia', 'Baritan', 'Bayan-bayanan', 'Catmon', 'Concepcion', 'Dampalit', 'Flores', 'Hulong Duhat', 'Muzon', 'Panghulo', 'Potrero', 'San Agustin', 'Tañong', 'Tinajeros' ),
		'Mandaluyong'       => array( 'Barangka Drive', 'Barangka Ilaya', 'Barangka Itaas', 'Barangka Ibaba', 'Buayang Bato', 'Hagdan Bato Itaas', 'Hagdan Bato Libis', 'Highway Hills', 'Hulo', 'Mauway', 'Namayan', 'New Zaniga', 'Old Zaniga', 'Pag-asa', 'Plainview', 'Pleasant Hills', 'Poblacion', 'San Jose', 'Vergara' ),
		'Manila'            => array( 'Binondo', 'Ermita', 'Intramuros', 'Malate', 'Paco', 'Pandacan', 'Port Area', 'Quiapo', 'Sampaloc', 'San Andres', 'San Miguel', 'San Nicolas', 'Santa Ana', 'Santa Cruz', 'Santa Mesa', 'Tondo' ),
		'Marikina'          => array( 'Barangka', 'Calumpang', 'Concepcion Uno', 'Concepcion Dos', 'Fortune', 'Industrial Valley', 'Jesus dela Peña', 'Kalumpang', 'Malanday', 'Nangka', 'Parang', 'San Roque', 'Santa Elena', 'Tañong' ),
		'Muntinlupa'        => array( 'Alabang', 'Ayala Alabang', 'Bayanan', 'Buli', 'Cupang', 'New Alabang Village', 'Putatan', 'Sucat', 'Tunasan' ),
		'Navotas'           => array( 'Bagumbayan North', 'Bagumbayan South', 'Bangculasi', 'Daanghari', 'Navotas East', 'Navotas West', 'North Bay Boulevard North', 'North Bay Boulevard South', 'San Jose', 'San Rafael Village', 'San Roque', 'Sipac-Almacen', 'Tanza' ),
		'Parañaque'         => array( 'Baclaran', 'BF Homes', 'Don Galo', 'La Huerta', 'Marcelo Green', 'Moonwalk', 'San Antonio', 'San Dionisio', 'San Isidro', 'San Martin de Porres', 'Santo Niño', 'Sun Valley', 'Tambo' ),
		'Pasay'             => array( 'Abad Santos', 'Baclaran', 'Libertad', 'Malibay', 'Pasay Rotonda', 'San Isidro', 'Tramo', 'Victory Heights' ),
		'Pasig'             => array( 'Bagong Ilog', 'Bagong Katipunan', 'Bambang', 'Buting', 'Caniogan', 'Dela Paz', 'Kalawaan', 'Kapitolyo', 'Manggahan', 'Maybunga', 'Oranbo', 'Palatiw', 'Pinagbuhatan', 'Pineda', 'Rosario', 'Sagad', 'San Antonio', 'San Joaquin', 'San Jose', 'San Miguel', 'Santa Cruz', 'Santa Lucia', 'Santa Rosa', 'Santo Tomas', 'Ugong' ),
		'Pateros'           => array( 'Aguho', 'Magtanggol', 'Martires del 96', 'Poblacion', 'San Pedro', 'San Roque', 'Santa Ana', 'Tabacalera' ),
		'Quezon City'       => array( 'Alicia', 'Bagbag', 'Bagong Silangan', 'Bagumbayan', 'Bahay Toro', 'Batasan Hills', 'Commonwealth', 'Culiat', 'Diliman', 'Fairview', 'Holy Spirit', 'Kaligayahan', 'Loyola Heights', 'Maharlika', 'Novaliches Proper', 'Payatas', 'Project 4', 'Project 6', 'San Bartolome', 'Santa Lucia', 'Teacher\'s Village East', 'Teacher\'s Village West', 'Ugong Norte', 'UP Campus', 'Vasra', 'West Triangle' ),
		'San Juan'          => array( 'Addition Hills', 'Balong-Bato', 'Corazon de Jesus', 'Ermitaño', 'Kabayanan', 'Little Baguio', 'Maytunas', 'Onse', 'Pasadeña', 'Pedro Cruz', 'Progreso', 'Rivera', 'Salapan', 'San Perfecto', 'Santa Lucia', 'Tibagan', 'West Crame' ),
		'Taguig'            => array( 'Bagong Tanyag', 'Bambang', 'Central Bicutan', 'Central Signal Village', 'Fort Bonifacio', 'Hagonoy', 'Ibayo-Tipas', 'Katuparan', 'Ligid-Tipas', 'Lower Bicutan', 'New Lower Bicutan', 'North Daang Hari', 'North Signal Village', 'Palingon', 'Pinagsama', 'San Miguel', 'Santa Ana', 'South Daang Hari', 'South Signal Village', 'Tanyag', 'Tuktukan', 'Upper Bicutan', 'Ususan', 'Wawa', 'Western Bicutan' ),
		'Valenzuela'        => array( 'Arkong Bato', 'Bagbaguin', 'Balangkas', 'Bignay', 'Bisig', 'Canumay East', 'Canumay West', 'Coloong', 'Dalandanan', 'Gen. T. de Leon', 'Isla', 'Karuhatan', 'Lawang Bato', 'Lingunan', 'Mabolo', 'Malanday', 'Malinta', 'Mapulang Lupa', 'Marulas', 'Maysan', 'Palasan', 'Parada', 'Paso de Blas', 'Pasolo', 'Poblacion', 'Polo', 'Punturin', 'Rincon', 'Tagalag', 'Ugong', 'Viente Reales', 'Wawang Pulo' ),
	),

	// ── Cebu ─────────────────────────────────────────────────────────────────
	'cebu' => array(
		'Cebu City'         => array( 'Apas', 'Banilad', 'Capitol Site', 'Carreta', 'Cogon Ramos', 'Guadalupe', 'Labangon', 'Lahug', 'Mabolo', 'Malubog', 'Pagsabungan', 'Pardo', 'Pasil', 'Poblacion Pardo', 'Sambag I', 'Sambag II', 'San Antonio', 'Santa Cruz', 'Talamban', 'T. Padilla' ),
		'Mandaue'           => array( 'Alang-alang', 'Bakilid', 'Banilad', 'Basak', 'Cambaro', 'Canduman', 'Casili', 'Casuntingan', 'Centro', 'Cubacub', 'Guizo', 'Ibabao-Estancia', 'Jagobiao', 'Labogon', 'Looc', 'Maguikay', 'Mantuyong', 'Opao', 'Pakna-an', 'Paknaan', 'Subangdaku', 'Tabok', 'Tawason', 'Tingub', 'Tipolo', 'Umapad' ),
		'Lapu-Lapu'         => array( 'Agus', 'Babag', 'Bankal', 'Baring', 'Basak', 'Buaya', 'Calawisan', 'Canjulao', 'Caw-oy', 'Cawhagan', 'Gun-ob', 'Ibo', 'Looc', 'Mactan', 'Maribago', 'Marigondon', 'Pajac', 'Pajo', 'Pangan-an', 'Poblacion', 'Punta Engaño', 'Pusok', 'Sabang', 'Santa Rosa', 'Subabasbas', 'Talima', 'Tingo', 'Tungasan' ),
	),

	// ── Davao del Sur ────────────────────────────────────────────────────────
	'davao-del-sur' => array(
		'Davao City'        => array( 'Agdao', 'Bajada', 'Buhangin', 'Bunawan', 'Centro', 'Cugman', 'Daliao', 'Dumoy', 'Indangan', 'Lacson', 'Lizada', 'Maa', 'Macasandig', 'Mapula', 'Matina Aplaya', 'Matina Crossing', 'Matina Pangi', 'Mintal', 'Pampanga', 'Panacan', 'Poblacion', 'Sasa', 'Talomo', 'Tibungco', 'Toril', 'Tugbok', 'Ulas' ),
	),

	// ── Laguna ───────────────────────────────────────────────────────────────
	'laguna' => array(
		'San Pedro'         => array( 'Bagong Silang', 'Calendola', 'Chrysanthemum', 'Cuyab', 'Estrella', 'Fatima', 'G.S.I.S.', 'Landayan', 'Langgam', 'Laram', 'Magsaysay', 'Maharlika', 'Narra', 'New Sto. Tomas', 'Pacita I', 'Pacita II', 'Poblacion', 'Riverside', 'Rosario', 'Sampaguita Village', 'San Antonio', 'San Lorenzo Ruiz', 'San Vicente', 'United Bayanihan', 'United Better Living' ),
		'Biñan'             => array( 'Bungahan', 'Canlalay', 'Casile', 'De La Paz', 'Ganado', 'Imus Approximation', 'Langkiwa', 'Loma', 'Malaban', 'Malamig', 'Mampalasan', 'Platero', 'Poblacion', 'San Antonio', 'San Francisco', 'San Jose', 'Santo Tomas', 'Soro-soro Ibaba', 'Soro-soro Ilaya', 'Soro-soro Katipunan', 'Timbao', 'Tubigan', 'Zapote' ),
		'Calamba'           => array( 'Bagong Kalsada', 'Banadero', 'Banlic', 'Batino', 'Burol', 'Burol II', 'Burol III', 'Laguerta', 'Lawa', 'Lecheria', 'Lingga', 'Looc', 'Mabato', 'Makiling', 'Mapagong', 'Masili', 'Maunong', 'Mayapa', 'Milagrosa', 'Palo Alto', 'Parian', 'Pasong Camachile I', 'Pasong Camachile II', 'Pasong Putik', 'Pulo', 'Punta', 'Real', 'Saimsim', 'Sampiruhan', 'San Cristobal', 'San Jose', 'Sirang Lupa', 'Sucol', 'Turbina', 'Ulango', 'Uwisan' ),
	),

	// ── Bulacan ──────────────────────────────────────────────────────────────
	'bulacan' => array(
		'Meycauayan'        => array( 'Bagbaguin', 'Bahay Pare', 'Bancal', 'Banga', 'Bayugo', 'Caingin', 'Caloocan', 'Camalig', 'Gasak', 'Hulo', 'Iba', 'Langka', 'Lawa', 'Libtong', 'Liputan', 'Longos', 'Malhacan', 'Pajo', 'Pandayan', 'Pantoc', 'Perez', 'Poblacion', 'Saluysoy', 'Saint Francis Village', 'Turo', 'Ubihan', 'Zamora' ),
		'Marilao'           => array( 'Abangan Norte', 'Abangan Sur', 'Ibayo', 'Lambakin', 'Lias', 'Loma de Gato', 'Nagbalon', 'Patubig', 'Pobacion I', 'Pobacion II', 'Saog', 'Santa Rosa I', 'Santa Rosa II', 'Tabing Ilog' ),
		'Malolos'           => array( 'Atlag', 'Babatnin', 'Bagna', 'Bagong Bayan', 'Balayong', 'Balite', 'Bangkal', 'Barihan', 'Bulihan', 'Bungahan', 'Caingin', 'Calero', 'Calizon', 'Camachilihan', 'Catmon', 'Cofradia', 'Dakila', 'Guinhawa', 'Liang', 'Ligas', 'Look 1st', 'Look 2nd', 'Lugam', 'Mabolo', 'Mambog', 'Masile', 'Matimbo', 'Mojon', 'Namayan', 'Niugan', 'Pamarawan', 'Panasahan', 'Pinambaran', 'Poblacion', 'Salapungan', 'Santor', 'Santo Rosario', 'Santisima Trinidad', 'Sumapang Bata', 'Sumapang Matanda', 'Taal', 'Tikay' ),
	),

	// ── Rizal ────────────────────────────────────────────────────────────────
	'rizal' => array(
		'Antipolo'          => array( 'Bagong Nayon', 'Beverly Hills', 'Calawis', 'Cupang', 'Dalig', 'Del Pilar', 'Dela Paz', 'Inarawan', 'Mambugan', 'Mayamot', 'Muntingdilaw', 'San Isidro', 'San Jose', 'San Juan', 'San Luis', 'San Roque', 'San Vicente', 'Sta Cruz' ),
		'Cainta'            => array( 'Antipolo Village', 'Batingan', 'Dalig', 'Del Pilar', 'Kalinangan', 'Liwayway', 'Parang', 'Poblacion I', 'Poblacion II', 'Poblacion III', 'San Andres', 'San Juan', 'Santa Rosa', 'Santo Domingo' ),
		'Taytay'            => array( 'Dolores', 'Muzon', 'Poblacion', 'San Isidro', 'Santa Ana' ),
	),

	// ── Pampanga ─────────────────────────────────────────────────────────────
	'pampanga' => array(
		'Angeles'           => array( 'Agapito del Rosario', 'Amsic', 'Anunas', 'Balibago', 'Capaya', 'Claro M. Recto', 'Cuayan', 'Cutcut I', 'Cutcut II', 'Lourdes Norte', 'Lourdes Sur', 'Lourdes Sur East', 'Malabanias', 'Margot', 'Mining', 'Pampang', 'Pandan', 'Pulung Cacutud', 'Pulung Maragul', 'Pulungbulu', 'Salapungan', 'San Jose', 'San Nicolas', 'Santa Teresita', 'Santa Trinidad', 'Santo Cristo', 'Santo Domingo', 'Santo Rosario', 'Sapalibutad', 'Sapangbato', 'Tabun', 'Virgen Delos Remedios' ),
		'San Fernando'      => array( 'Alasas', 'Baliti', 'Bulaon', 'Calulut', 'Del Carmen', 'Del Pilar', 'Del Rosario', 'Dela Paz Norte', 'Dela Paz Sur', 'Dolores', 'Juliana', 'Lara', 'Lourdes', 'Magliman', 'Maimpis', 'Malino', 'Malpitic', 'Pandaras', 'Panipuan', 'Pulung Bulu', 'Quebiawan', 'Saguin', 'San Agustin', 'San Felipe', 'San Isidro', 'San Jose', 'San Juan', 'San Nicolas', 'San Pedro', 'Santa Lucia', 'Santo Niño', 'Sindalan', 'Telabastagan' ),
	),

);
