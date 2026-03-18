<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\Admin\User;
use App\Models\Admin\UserInformation;
use App\Models\Departamento;
use App\Models\Municipio;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // DEPARTAMENTOS

            Departamento::create([
                'nombre' => ucfirst('alta verapaz')
            ]);
            Departamento::create([
                'nombre' => ucfirst('baja verapaz')
            ]);
            Departamento::create([
                'nombre' => ucfirst('chimaltenango')
            ]);
            Departamento::create([
                'nombre' => ucfirst('chiquimula')
            ]);
            Departamento::create([
                'nombre' => ucfirst('el progreso')
            ]);
            Departamento::create([
                'nombre' => ucfirst('escuintla')
            ]);
            Departamento::create([
                'nombre' => ucfirst('guatemala')
            ]);
            Departamento::create([
                'nombre' => ucfirst('huehuetenango')
            ]);
            Departamento::create([
                'nombre' => ucfirst('izabal')
            ]);
            Departamento::create([
                'nombre' => ucfirst('jalapa')
            ]);
            Departamento::create([
                'nombre' => ucfirst('jutiapa')
            ]);
            Departamento::create([
                'nombre' => ucfirst('petén')
            ]);
            Departamento::create([
                'nombre' => ucfirst('quetzaltenango')
            ]);
            Departamento::create([
                'nombre' => ucfirst('quiché')
            ]);
            Departamento::create([
                'nombre' => ucfirst('retalhuleu')
            ]);
            Departamento::create([
                'nombre' => ucfirst('sacatepéquez')
            ]);
            Departamento::create([
                'nombre' => ucfirst('san marcos')
            ]);
            Departamento::create([
                'nombre' => ucfirst('santa rosa')
            ]);
            Departamento::create([
                'nombre' => ucfirst('sololá')
            ]);
            Departamento::create([
                'nombre' => ucfirst('suchitepéquez')
            ]);
            Departamento::create([
                'nombre' => ucfirst('totonicapán')
            ]);
            Departamento::create([
                'nombre' => ucfirst('zacapa')
            ]);
        // END DEPARTAMENTOS

        // MUNICIPIOS

            Municipio::create([
                'nombre' => ucwords('Cobán'),
                'departamento_id' => 	1
            ]);
            Municipio::create([
                'nombre' => ucwords('Santa cruz verapaz'),
                'departamento_id' => 	1
            ]);
            Municipio::create([
                'nombre' => ucwords('San cristóbal verapaz'),
                'departamento_id' => 	1
            ]);
            Municipio::create([
                'nombre' => ucwords('Tactic'),
                'departamento_id' => 	1
            ]);
            Municipio::create([
                'nombre' => ucwords('Tamahú'),
                'departamento_id' => 	1
            ]);
            Municipio::create([
                'nombre' => ucwords('San miguel tucurú'),
                'departamento_id' => 	1
            ]);
            Municipio::create([
                'nombre' => ucwords('Panzóz'),
                'departamento_id' => 	1
            ]);
            Municipio::create([
                'nombre' => ucwords('Senahú.'),
                'departamento_id' => 	1
            ]);
            Municipio::create([
                'nombre' => ucwords('San pedro carchá'),
                'departamento_id' => 	1
            ]);
            Municipio::create([
                'nombre' => ucwords('San juan chamelco'),
                'departamento_id' => 	1
            ]);
            Municipio::create([
                'nombre' => ucwords('San agustín lanquín'),
                'departamento_id' => 	1
            ]);
            Municipio::create([
                'nombre' => ucwords('Santa maría cahabón'),
                'departamento_id' => 	1
            ]);
            Municipio::create([
                'nombre' => ucwords('Chisec'),
                'departamento_id' => 	1
            ]);
            Municipio::create([
                'nombre' => ucwords('Chahal'),
                'departamento_id' => 	1
            ]);
            Municipio::create([
                'nombre' => ucwords('Fray bartolomé de las casas'),
                'departamento_id' => 	1
            ]);
            Municipio::create([
                'nombre' => ucwords('Santa catalina la tinta'),
                'departamento_id' => 	1
            ]);
            Municipio::create([
                'nombre' => ucwords('Raxruhá'),
                'departamento_id' => 	1
            ]);
            Municipio::create([
                'nombre' => ucwords('Salamá'),
                'departamento_id' => 	2
            ]);
            Municipio::create([
                'nombre' => ucwords('San miguel chicaj'),
                'departamento_id' => 	2
            ]);
            Municipio::create([
                'nombre' => ucwords('Rabinal'),
                'departamento_id' => 	2
            ]);
            Municipio::create([
                'nombre' => ucwords('Cubulco'),
                'departamento_id' => 	2
            ]);
            Municipio::create([
                'nombre' => ucwords('Granados'),
                'departamento_id' => 	2
            ]);
            Municipio::create([
                'nombre' => ucwords('Santa cruz el chol'),
                'departamento_id' => 	2
            ]);
            Municipio::create([
                'nombre' => ucwords('San jerónimo'),
                'departamento_id' => 	2
            ]);
            Municipio::create([
                'nombre' => ucwords('Purulhá'),
                'departamento_id' => 	2
            ]);
            Municipio::create([
                'nombre' => ucwords('Chimaltenango'),
                'departamento_id' => 	3
            ]);
            Municipio::create([
                'nombre' => ucwords('San josé poaquil'),
                'departamento_id' => 	3
            ]);
            Municipio::create([
                'nombre' => ucwords('San martín jilotepeque'),
                'departamento_id' => 	3
            ]);
            Municipio::create([
                'nombre' => ucwords('San juan comalapa'),
                'departamento_id' => 	3
            ]);
            Municipio::create([
                'nombre' => ucwords('Santa apolonia'),
                'departamento_id' => 	3
            ]);
            Municipio::create([
                'nombre' => ucwords('Tecpán'),
                'departamento_id' => 	3
            ]);
            Municipio::create([
                'nombre' => ucwords('Patzún'),
                'departamento_id' => 	3
            ]);
            Municipio::create([
                'nombre' => ucwords('San miguel pochuta'),
                'departamento_id' => 	3
            ]);
            Municipio::create([
                'nombre' => ucwords('Patzicía'),
                'departamento_id' => 	3
            ]);
            Municipio::create([
                'nombre' => ucwords('Santa cruz balanyá'),
                'departamento_id' => 	3
            ]);
            Municipio::create([
                'nombre' => ucwords('Acatenango'),
                'departamento_id' => 	3
            ]);
            Municipio::create([
                'nombre' => ucwords('San pedro yepocapa'),
                'departamento_id' => 	3
            ]);
            Municipio::create([
                'nombre' => ucwords('San andrés itzapa'),
                'departamento_id' => 	3
            ]);
            Municipio::create([
                'nombre' => ucwords('Parramos'),
                'departamento_id' => 	3
            ]);
            Municipio::create([
                'nombre' => ucwords('Zaragoza'),
                'departamento_id' => 	3
            ]);
            Municipio::create([
                'nombre' => ucwords('El tejar'),
                'departamento_id' => 	3
            ]);
            Municipio::create([
                'nombre' => ucwords('Chiquimula'),
                'departamento_id' => 	4
            ]);
            Municipio::create([
                'nombre' => ucwords('Jocotán'),
                'departamento_id' => 	4
            ]);
            Municipio::create([
                'nombre' => ucwords('Esquipulas'),
                'departamento_id' => 	4
            ]);
            Municipio::create([
                'nombre' => ucwords('Camotán'),
                'departamento_id' => 	4
            ]);
            Municipio::create([
                'nombre' => ucwords('Quezaltepeque'),
                'departamento_id' => 	4
            ]);
            Municipio::create([
                'nombre' => ucwords('Olopa'),
                'departamento_id' => 	4
            ]);
            Municipio::create([
                'nombre' => ucwords('Ipala'),
                'departamento_id' => 	4
            ]);
            Municipio::create([
                'nombre' => ucwords('San juan ermita'),
                'departamento_id' => 	4
            ]);
            Municipio::create([
                'nombre' => ucwords('Concepción las minas'),
                'departamento_id' => 	4
            ]);
            Municipio::create([
                'nombre' => ucwords('San jacinto'),
                'departamento_id' => 	4
            ]);
            Municipio::create([
                'nombre' => ucwords('San josé la arada'),
                'departamento_id' => 	4
            ]);
            Municipio::create([
                'nombre' => ucwords('El jícaro'),
                'departamento_id' => 	5
            ]);
            Municipio::create([
                'nombre' => ucwords('Morazán'),
                'departamento_id' => 	5
            ]);
            Municipio::create([
                'nombre' => ucwords('San agustín acasaguastlán'),
                'departamento_id' => 	5
            ]);
            Municipio::create([
                'nombre' => ucwords('San antonio la paz'),
                'departamento_id' => 	5
            ]);
            Municipio::create([
                'nombre' => ucwords('San cristóbal acasaguastlán'),
                'departamento_id' => 	5
            ]);
            Municipio::create([
                'nombre' => ucwords('Sanarate'),
                'departamento_id' => 	5
            ]);
            Municipio::create([
                'nombre' => ucwords('Guastatoya'),
                'departamento_id' => 	5
            ]);
            Municipio::create([
                'nombre' => ucwords('Sansare'),
                'departamento_id' => 	5
            ]);
            Municipio::create([
                'nombre' => ucwords('Escuintla'),
                'departamento_id' => 	6
            ]);
            Municipio::create([
                'nombre' => ucwords('Santa lucía cotzumalguapa'),
                'departamento_id' => 	6
            ]);
            Municipio::create([
                'nombre' => ucwords('La democracia'),
                'departamento_id' => 	6
            ]);
            Municipio::create([
                'nombre' => ucwords('Siquinalá'),
                'departamento_id' => 	6
            ]);
            Municipio::create([
                'nombre' => ucwords('Masagua'),
                'departamento_id' => 	6
            ]);
            Municipio::create([
                'nombre' => ucwords('Tiquisate'),
                'departamento_id' => 	6
            ]);
            Municipio::create([
                'nombre' => ucwords('La gomera'),
                'departamento_id' => 	6
            ]);
            Municipio::create([
                'nombre' => ucwords('Guaganazapa'),
                'departamento_id' => 	6
            ]);
            Municipio::create([
                'nombre' => ucwords('San josé'),
                'departamento_id' => 	6
            ]);
            Municipio::create([
                'nombre' => ucwords('Iztapa'),
                'departamento_id' => 	6
            ]);
            Municipio::create([
                'nombre' => ucwords('Palín'),
                'departamento_id' => 	6
            ]);
            Municipio::create([
                'nombre' => ucwords('San vicente pacaya'),
                'departamento_id' => 	6
            ]);
            Municipio::create([
                'nombre' => ucwords('Nueva concepción'),
                'departamento_id' => 	6
            ]);
            Municipio::create([
                'nombre' => ucwords('Santa catarina pinula'),
                'departamento_id' => 	7
            ]);
            Municipio::create([
                'nombre' => ucwords('San josé pinula'),
                'departamento_id' => 	7
            ]);
            Municipio::create([
                'nombre' => ucwords('Guatemala'),
                'departamento_id' => 	7
            ]);
            Municipio::create([
                'nombre' => ucwords('San josé del golfo'),
                'departamento_id' => 	7
            ]);
            Municipio::create([
                'nombre' => ucwords('Palencia'),
                'departamento_id' => 	7
            ]);
            Municipio::create([
                'nombre' => ucwords('Chinautla'),
                'departamento_id' => 	7
            ]);
            Municipio::create([
                'nombre' => ucwords('San pedro ayampuc'),
                'departamento_id' => 	7
            ]);
            Municipio::create([
                'nombre' => ucwords('Mixco'),
                'departamento_id' => 	7
            ]);
            Municipio::create([
                'nombre' => ucwords('San pedro sacatapéquez'),
                'departamento_id' => 	7
            ]);
            Municipio::create([
                'nombre' => ucwords('San juan sacatepéquez'),
                'departamento_id' => 	7
            ]);
            Municipio::create([
                'nombre' => ucwords('Chuarrancho'),
                'departamento_id' => 	7
            ]);
            Municipio::create([
                'nombre' => ucwords('San raymundo'),
                'departamento_id' => 	7
            ]);
            Municipio::create([
                'nombre' => ucwords('Fraijanes'),
                'departamento_id' => 	7
            ]);
            Municipio::create([
                'nombre' => ucwords('Amatitlán'),
                'departamento_id' => 	7
            ]);
            Municipio::create([
                'nombre' => ucwords('Villa nueva'),
                'departamento_id' => 	7
            ]);
            Municipio::create([
                'nombre' => ucwords('Villa canales'),
                'departamento_id' => 	7
            ]);
            Municipio::create([
                'nombre' => ucwords('San miguel petapa'),
                'departamento_id' => 	7
            ]);
            Municipio::create([
                'nombre' => ucwords('Huehuetenango'),
                'departamento_id' => 	8
            ]);
            Municipio::create([
                'nombre' => ucwords('Chiantla'),
                'departamento_id' => 	8
            ]);
            Municipio::create([
                'nombre' => ucwords('Malacatancito'),
                'departamento_id' => 	8
            ]);
            Municipio::create([
                'nombre' => ucwords('Cuilco'),
                'departamento_id' => 	8
            ]);
            Municipio::create([
                'nombre' => ucwords('Nentón'),
                'departamento_id' => 	8
            ]);
            Municipio::create([
                'nombre' => ucwords('San pedro necta'),
                'departamento_id' => 	8
            ]);
            Municipio::create([
                'nombre' => ucwords('Jacaltenango'),
                'departamento_id' => 	8
            ]);
            Municipio::create([
                'nombre' => ucwords('Soloma'),
                'departamento_id' => 	8
            ]);
            Municipio::create([
                'nombre' => ucwords('Ixtahuacán'),
                'departamento_id' => 	8
            ]);
            Municipio::create([
                'nombre' => ucwords('Santa bárbara'),
                'departamento_id' => 	8
            ]);
            Municipio::create([
                'nombre' => ucwords('La libertad'),
                'departamento_id' => 	8
            ]);
            Municipio::create([
                'nombre' => ucwords('La democracia'),
                'departamento_id' => 	8
            ]);
            Municipio::create([
                'nombre' => ucwords('San miguel acatán'),
                'departamento_id' => 	8
            ]);
            Municipio::create([
                'nombre' => ucwords('San rafael la independencia'),
                'departamento_id' => 	8
            ]);
            Municipio::create([
                'nombre' => ucwords('Todos santos cuchumatán'),
                'departamento_id' => 	8
            ]);
            Municipio::create([
                'nombre' => ucwords('San juan atitlán'),
                'departamento_id' => 	8
            ]);
            Municipio::create([
                'nombre' => ucwords('Santa eulalia'),
                'departamento_id' => 	8
            ]);
            Municipio::create([
                'nombre' => ucwords('San mateo ixtatán'),
                'departamento_id' => 	8
            ]);
            Municipio::create([
                'nombre' => ucwords('Colotenango'),
                'departamento_id' => 	8
            ]);
            Municipio::create([
                'nombre' => ucwords('San sebastián huehuetenango'),
                'departamento_id' => 	8
            ]);
            Municipio::create([
                'nombre' => ucwords('Tectitán'),
                'departamento_id' => 	8
            ]);
            Municipio::create([
                'nombre' => ucwords('Concepción huista'),
                'departamento_id' => 	8
            ]);
            Municipio::create([
                'nombre' => ucwords('San juan ixcoy'),
                'departamento_id' => 	8
            ]);
            Municipio::create([
                'nombre' => ucwords('San antonio huista'),
                'departamento_id' => 	8
            ]);
            Municipio::create([
                'nombre' => ucwords('Santa cruz barillas'),
                'departamento_id' => 	8
            ]);
            Municipio::create([
                'nombre' => ucwords('San sebastián coatán'),
                'departamento_id' => 	8
            ]);
            Municipio::create([
                'nombre' => ucwords('Aguacatán'),
                'departamento_id' => 	8
            ]);
            Municipio::create([
                'nombre' => ucwords('San rafael petzal'),
                'departamento_id' => 	8
            ]);
            Municipio::create([
                'nombre' => ucwords('San gaspar ixchil'),
                'departamento_id' => 	8
            ]);
            Municipio::create([
                'nombre' => ucwords('Santiago chimaltenango'),
                'departamento_id' => 	8
            ]);
            Municipio::create([
                'nombre' => ucwords('Santa ana huista'),
                'departamento_id' => 	8
            ]);
            Municipio::create([
                'nombre' => ucwords('Morales'),
                'departamento_id' => 	9
            ]);
            Municipio::create([
                'nombre' => ucwords('Los amates'),
                'departamento_id' => 	9
            ]);
            Municipio::create([
                'nombre' => ucwords('Livingston'),
                'departamento_id' => 	9
            ]);
            Municipio::create([
                'nombre' => ucwords('El estor'),
                'departamento_id' => 	9
            ]);
            Municipio::create([
                'nombre' => ucwords('Puerto barrios'),
                'departamento_id' => 	9
            ]);
            Municipio::create([
                'nombre' => ucwords('Jalapa'),
                'departamento_id' => 10
            ]);
            Municipio::create([
                'nombre' => ucwords('San pedro pinula'),
                'departamento_id' => 10
            ]);
            Municipio::create([
                'nombre' => ucwords('San luis jilotepeque'),
                'departamento_id' => 10
            ]);
            Municipio::create([
                'nombre' => ucwords('San manuel chaparrón'),
                'departamento_id' => 10
            ]);
            Municipio::create([
                'nombre' => ucwords('San carlos alzatate'),
                'departamento_id' => 10
            ]);
            Municipio::create([
                'nombre' => ucwords('Monjas'),
                'departamento_id' => 10
            ]);
            Municipio::create([
                'nombre' => ucwords('Mataquescuintla'),
                'departamento_id' => 10
            ]);

            Municipio::create([
                'nombre' => ucwords('Jutiapa'),
                'departamento_id' => 11
            ]);
            Municipio::create([
                'nombre' => ucwords('El progreso'),
                'departamento_id' => 11
            ]);
            Municipio::create([
                'nombre' => ucwords('Santa catarina mita'),
                'departamento_id' => 11
            ]);
            Municipio::create([
                'nombre' => ucwords('Agua blanca'),
                'departamento_id' => 11
            ]);
            Municipio::create([
                'nombre' => ucwords('Asunción mita'),
                'departamento_id' => 11
            ]);
            Municipio::create([
                'nombre' => ucwords('Yupiltepeque'),
                'departamento_id' => 11
            ]);
            Municipio::create([
                'nombre' => ucwords('Atescatempa'),
                'departamento_id' => 11
            ]);
            Municipio::create([
                'nombre' => ucwords('Jerez'),
                'departamento_id' => 11
            ]);
            Municipio::create([
                'nombre' => ucwords('El adelanto'),
                'departamento_id' => 11
            ]);
            Municipio::create([
                'nombre' => ucwords('Zapotitlán'),
                'departamento_id' => 11
            ]);
            Municipio::create([
                'nombre' => ucwords('Comapa'),
                'departamento_id' => 11
            ]);
            Municipio::create([
                'nombre' => ucwords('Jalpatagua'),
                'departamento_id' => 11
            ]);
            Municipio::create([
                'nombre' => ucwords('Conguaco'),
                'departamento_id' => 11
            ]);
            Municipio::create([
                'nombre' => ucwords('Moyuta'),
                'departamento_id' => 11
            ]);
            Municipio::create([
                'nombre' => ucwords('Pasaco'),
                'departamento_id' => 11
            ]);
            Municipio::create([
                'nombre' => ucwords('Quesada'),
                'departamento_id' => 11
            ]);
            Municipio::create([
                'nombre' => ucwords('Flores'),
                'departamento_id' => 12
            ]);
            Municipio::create([
                'nombre' => ucwords('San josé'),
                'departamento_id' => 12
            ]);
            Municipio::create([
                'nombre' => ucwords('San benito'),
                'departamento_id' => 12
            ]);
            Municipio::create([
                'nombre' => ucwords('San andrés'),
                'departamento_id' => 12
            ]);
            Municipio::create([
                'nombre' => ucwords('La libertad'),
                'departamento_id' => 12
            ]);
            Municipio::create([
                'nombre' => ucwords('San francisco'),
                'departamento_id' => 12
            ]);
            Municipio::create([
                'nombre' => ucwords('Santa ana'),
                'departamento_id' => 12
            ]);
            Municipio::create([
                'nombre' => ucwords('Dolores'),
                'departamento_id' => 12
            ]);
            Municipio::create([
                'nombre' => ucwords('San luis'),
                'departamento_id' => 12
            ]);
            Municipio::create([
                'nombre' => ucwords('Sayaxché'),
                'departamento_id' => 12
            ]);
            Municipio::create([
                'nombre' => ucwords('Melchor de mencos'),
                'departamento_id' => 12
            ]);
            Municipio::create([
                'nombre' => ucwords('Poptún'),
                'departamento_id' => 12
            ]);
            Municipio::create([
                'nombre' => ucwords('Quetzaltenango'),
                'departamento_id' => 13
            ]);
            Municipio::create([
                'nombre' => ucwords('Salcajá'),
                'departamento_id' => 13
            ]);
            Municipio::create([
                'nombre' => ucwords('San juan olintepeque'),
                'departamento_id' => 13
            ]);
            Municipio::create([
                'nombre' => ucwords('San carlos sija'),
                'departamento_id' => 13
            ]);
            Municipio::create([
                'nombre' => ucwords('Sibilia'),
                'departamento_id' => 13
            ]);
            Municipio::create([
                'nombre' => ucwords('Cabricán'),
                'departamento_id' => 13
            ]);
            Municipio::create([
                'nombre' => ucwords('Cajolá'),
                'departamento_id' => 13
            ]);
            Municipio::create([
                'nombre' => ucwords('San miguel siguilá'),
                'departamento_id' => 13
            ]);
            Municipio::create([
                'nombre' => ucwords('San juan ostuncalco'),
                'departamento_id' => 13
            ]);
            Municipio::create([
                'nombre' => ucwords('San mateo'),
                'departamento_id' => 13
            ]);
            Municipio::create([
                'nombre' => ucwords('Concepción chiquirichapa'),
                'departamento_id' => 13
            ]);
            Municipio::create([
                'nombre' => ucwords('San martín sacatepéquez'),
                'departamento_id' => 13
            ]);
            Municipio::create([
                'nombre' => ucwords('Almolonga'),
                'departamento_id' => 13
            ]);
            Municipio::create([
                'nombre' => ucwords('Cantel'),
                'departamento_id' => 13
            ]);
            Municipio::create([
                'nombre' => ucwords('Huitán'),
                'departamento_id' => 13
            ]);
            Municipio::create([
                'nombre' => ucwords('Zunil'),
                'departamento_id' => 13
            ]);
            Municipio::create([
                'nombre' => ucwords('Colomba costa cuca'),
                'departamento_id' => 13
            ]);
            Municipio::create([
                'nombre' => ucwords('San francisco la unión'),
                'departamento_id' => 13
            ]);
            Municipio::create([
                'nombre' => ucwords('El palmar'),
                'departamento_id' => 13
            ]);
            Municipio::create([
                'nombre' => ucwords('Coatepeque'),
                'departamento_id' => 13
            ]);
            Municipio::create([
                'nombre' => ucwords('Génova'),
                'departamento_id' => 13
            ]);
            Municipio::create([
                'nombre' => ucwords('Flores costa cuca'),
                'departamento_id' => 13
            ]);
            Municipio::create([
                'nombre' => ucwords('La esperanza'),
                'departamento_id' => 13
            ]);
            Municipio::create([
                'nombre' => ucwords('Palestina de los altos'),
                'departamento_id' => 13
            ]);
            Municipio::create([
                'nombre' => ucwords('Santa cruz del quiché'),
                'departamento_id' => 14
            ]);
            Municipio::create([
                'nombre' => ucwords('Chiché'),
                'departamento_id' => 14
            ]);
            Municipio::create([
                'nombre' => ucwords('Chinique'),
                'departamento_id' => 14
            ]);
            Municipio::create([
                'nombre' => ucwords('Zacualpa'),
                'departamento_id' => 14
            ]);
            Municipio::create([
                'nombre' => ucwords('Chajul'),
                'departamento_id' => 14
            ]);
            Municipio::create([
                'nombre' => ucwords('Santo tomás chichicastenango'),
                'departamento_id' => 14
            ]);
            Municipio::create([
                'nombre' => ucwords('Patzité'),
                'departamento_id' => 14
            ]);
            Municipio::create([
                'nombre' => ucwords('San antonio ilotenango'),
                'departamento_id' => 14
            ]);
            Municipio::create([
                'nombre' => ucwords('San pedro jocopilas'),
                'departamento_id' => 14
            ]);
            Municipio::create([
                'nombre' => ucwords('Cunén'),
                'departamento_id' => 14
            ]);
            Municipio::create([
                'nombre' => ucwords('San juan cotzal'),
                'departamento_id' => 14
            ]);
            Municipio::create([
                'nombre' => ucwords('Santa maría joyabaj'),
                'departamento_id' => 14
            ]);
            Municipio::create([
                'nombre' => ucwords('Santa maría nebaj'),
                'departamento_id' => 14
            ]);
            Municipio::create([
                'nombre' => ucwords('San andrés sajcabajá'),
                'departamento_id' => 14
            ]);
            Municipio::create([
                'nombre' => ucwords('Uspantán'),
                'departamento_id' => 14
            ]);
            Municipio::create([
                'nombre' => ucwords('Sacapulas'),
                'departamento_id' => 14
            ]);
            Municipio::create([
                'nombre' => ucwords('San bartolomé jocotenango'),
                'departamento_id' => 14
            ]);
            Municipio::create([
                'nombre' => ucwords('Canillá'),
                'departamento_id' => 14
            ]);
            Municipio::create([
                'nombre' => ucwords('Chicamán'),
                'departamento_id' => 14
            ]);
            Municipio::create([
                'nombre' => ucwords('Ixcán'),
                'departamento_id' => 14
            ]);
            Municipio::create([
                'nombre' => ucwords('Pachalum'),
                'departamento_id' => 14
            ]);
            Municipio::create([
                'nombre' => ucwords('Retalhuleu'),
                'departamento_id' => 15
            ]);
            Municipio::create([
                'nombre' => ucwords('San sebastián'),
                'departamento_id' => 15
            ]);
            Municipio::create([
                'nombre' => ucwords('Santa cruz muluá'),
                'departamento_id' => 15
            ]);
            Municipio::create([
                'nombre' => ucwords('San martín zapotitlán'),
                'departamento_id' => 15
            ]);
            Municipio::create([
                'nombre' => ucwords('San felipe'),
                'departamento_id' => 15
            ]);
            Municipio::create([
                'nombre' => ucwords('San andrés villa seca'),
                'departamento_id' => 15
            ]);
            Municipio::create([
                'nombre' => ucwords('Champerico'),
                'departamento_id' => 15
            ]);
            Municipio::create([
                'nombre' => ucwords('Nuevo san carlos'),
                'departamento_id' => 15
            ]);
            Municipio::create([
                'nombre' => ucwords('El asintal'),
                'departamento_id' => 15
            ]);
            Municipio::create([
                'nombre' => ucwords('Antigua guatemala'),
                'departamento_id' => 16
            ]);
            Municipio::create([
                'nombre' => ucwords('Jocotenango'),
                'departamento_id' => 16
            ]);
            Municipio::create([
                'nombre' => ucwords('Pastores'),
                'departamento_id' => 16
            ]);
            Municipio::create([
                'nombre' => ucwords('Sumpango'),
                'departamento_id' => 16
            ]);
            Municipio::create([
                'nombre' => ucwords('Santo domingo xenacoj'),
                'departamento_id' => 16
            ]);
            Municipio::create([
                'nombre' => ucwords('Santiago sacatepéquez'),
                'departamento_id' => 16
            ]);
            Municipio::create([
                'nombre' => ucwords('San bartolomé milpas altas'),
                'departamento_id' => 16
            ]);
            Municipio::create([
                'nombre' => ucwords('San lucas sacatepéquez'),
                'departamento_id' => 16
            ]);
            Municipio::create([
                'nombre' => ucwords('Santa lucía milpas altas'),
                'departamento_id' => 16
            ]);
            Municipio::create([
                'nombre' => ucwords('Magdalena milpas altas'),
                'departamento_id' => 16
            ]);
            Municipio::create([
                'nombre' => ucwords('Santa maría de jesús'),
                'departamento_id' => 16
            ]);
            Municipio::create([
                'nombre' => ucwords('Ciudad vieja'),
                'departamento_id' => 16
            ]);
            Municipio::create([
                'nombre' => ucwords('San miguel dueñas'),
                'departamento_id' => 16
            ]);
            Municipio::create([
                'nombre' => ucwords('San juan alotenango'),
                'departamento_id' => 16
            ]);
            Municipio::create([
                'nombre' => ucwords('San antonio aguas calientes'),
                'departamento_id' => 16
            ]);
            Municipio::create([
                'nombre' => ucwords('Santa catarina barahona'),
                'departamento_id' => 16
            ]);
            Municipio::create([
                'nombre' => ucwords('San marcos'),
                'departamento_id' => 17
            ]);
            Municipio::create([
                'nombre' => ucwords('Ayutla'),
                'departamento_id' => 17
            ]);
            Municipio::create([
                'nombre' => ucwords('Catarina'),
                'departamento_id' => 17
            ]);
            Municipio::create([
                'nombre' => ucwords('Comitancillo'),
                'departamento_id' => 17
            ]);
            Municipio::create([
                'nombre' => ucwords('Concepción tutuapa'),
                'departamento_id' => 17
            ]);
            Municipio::create([
                'nombre' => ucwords('El quetzal'),
                'departamento_id' => 17
            ]);
            Municipio::create([
                'nombre' => ucwords('El rodeo'),
                'departamento_id' => 17
            ]);
            Municipio::create([
                'nombre' => ucwords('El tumblador'),
                'departamento_id' => 17
            ]);
            Municipio::create([
                'nombre' => ucwords('Ixchiguán'),
                'departamento_id' => 17
            ]);
            Municipio::create([
                'nombre' => ucwords('La reforma'),
                'departamento_id' => 17
            ]);
            Municipio::create([
                'nombre' => ucwords('Malacatán'),
                'departamento_id' => 17
            ]);
            Municipio::create([
                'nombre' => ucwords('Nuevo progreso'),
                'departamento_id' => 17
            ]);
            Municipio::create([
                'nombre' => ucwords('Ocós'),
                'departamento_id' => 17
            ]);
            Municipio::create([
                'nombre' => ucwords('Pajapita'),
                'departamento_id' => 17
            ]);
            Municipio::create([
                'nombre' => ucwords('Esquipulas palo gordo'),
                'departamento_id' => 17
            ]);
            Municipio::create([
                'nombre' => ucwords('San antonio sacatepéquez'),
                'departamento_id' => 17
            ]);
            Municipio::create([
                'nombre' => ucwords('San cristóbal cucho'),
                'departamento_id' => 17
            ]);
            Municipio::create([
                'nombre' => ucwords('San josé ojetenam'),
                'departamento_id' => 17
            ]);
            Municipio::create([
                'nombre' => ucwords('San lorenzo'),
                'departamento_id' => 17
            ]);
            Municipio::create([
                'nombre' => ucwords('San miguel ixtahuacán'),
                'departamento_id' => 17
            ]);
            Municipio::create([
                'nombre' => ucwords('San pablo'),
                'departamento_id' => 17
            ]);
            Municipio::create([
                'nombre' => ucwords('San pedro sacatepéquez'),
                'departamento_id' => 17
            ]);
            Municipio::create([
                'nombre' => ucwords('San rafael pie de la cuesta'),
                'departamento_id' => 17
            ]);
            Municipio::create([
                'nombre' => ucwords('Sibinal'),
                'departamento_id' => 17
            ]);
            Municipio::create([
                'nombre' => ucwords('Sipacapa'),
                'departamento_id' => 17
            ]);
            Municipio::create([
                'nombre' => ucwords('Tacaná'),
                'departamento_id' => 17
            ]);
            Municipio::create([
                'nombre' => ucwords('Tajumulco'),
                'departamento_id' => 17
            ]);
            Municipio::create([
                'nombre' => ucwords('Tejutla'),
                'departamento_id' => 17
            ]);
            Municipio::create([
                'nombre' => ucwords('Río blanco'),
                'departamento_id' => 17
            ]);
            Municipio::create([
                'nombre' => ucwords('La blanca'),
                'departamento_id' => 17
            ]);
            Municipio::create([
                'nombre' => ucwords('Cuilapa'),
                'departamento_id' => 18
            ]);
            Municipio::create([
                'nombre' => ucwords('Casillas santa rosa'),
                'departamento_id' => 18
            ]);
            Municipio::create([
                'nombre' => ucwords('Chiquimulilla'),
                'departamento_id' => 18
            ]);
            Municipio::create([
                'nombre' => ucwords('Guazacapán'),
                'departamento_id' => 18
            ]);
            Municipio::create([
                'nombre' => ucwords('Nueva santa rosa'),
                'departamento_id' => 18
            ]);
            Municipio::create([
                'nombre' => ucwords('Oratorio'),
                'departamento_id' => 18
            ]);
            Municipio::create([
                'nombre' => ucwords('Pueblo nuevo viñas'),
                'departamento_id' => 18
            ]);
            Municipio::create([
                'nombre' => ucwords('San juan tecuaco'),
                'departamento_id' => 18
            ]);
            Municipio::create([
                'nombre' => ucwords('San rafael las flores'),
                'departamento_id' => 18
            ]);
            Municipio::create([
                'nombre' => ucwords('Santa cruz naranjo'),
                'departamento_id' => 18
            ]);
            Municipio::create([
                'nombre' => ucwords('Santa maría ixhuatán'),
                'departamento_id' => 18
            ]);
            Municipio::create([
                'nombre' => ucwords('Santa rosa de lima'),
                'departamento_id' => 18
            ]);
            Municipio::create([
                'nombre' => ucwords('Taxisco'),
                'departamento_id' => 18
            ]);
            Municipio::create([
                'nombre' => ucwords('Barberena'),
                'departamento_id' => 18
            ]);
            Municipio::create([
                'nombre' => ucwords('Sololá'),
                'departamento_id' => 19
            ]);
            Municipio::create([
                'nombre' => ucwords('Concepción'),
                'departamento_id' => 19
            ]);
            Municipio::create([
                'nombre' => ucwords('Nahualá'),
                'departamento_id' => 19
            ]);
            Municipio::create([
                'nombre' => ucwords('Panajachel'),
                'departamento_id' => 19
            ]);
            Municipio::create([
                'nombre' => ucwords('San andrés semetabaj'),
                'departamento_id' => 19
            ]);
            Municipio::create([
                'nombre' => ucwords('San antonio palopó'),
                'departamento_id' => 19
            ]);
            Municipio::create([
                'nombre' => ucwords('San josé chacayá'),
                'departamento_id' => 19
            ]);
            Municipio::create([
                'nombre' => ucwords('San juan la laguna'),
                'departamento_id' => 19
            ]);
            Municipio::create([
                'nombre' => ucwords('San lucas tolimán'),
                'departamento_id' => 19
            ]);
            Municipio::create([
                'nombre' => ucwords('San marcos la laguna'),
                'departamento_id' => 19
            ]);
            Municipio::create([
                'nombre' => ucwords('San pablo la laguna'),
                'departamento_id' => 19
            ]);
            Municipio::create([
                'nombre' => ucwords('San pedro la laguna'),
                'departamento_id' => 19
            ]);
            Municipio::create([
                'nombre' => ucwords('Santa catarina ixtahuacán'),
                'departamento_id' => 19
            ]);
            Municipio::create([
                'nombre' => ucwords('Santa catarina palopó'),
                'departamento_id' => 19
            ]);
            Municipio::create([
                'nombre' => ucwords('Santa clara la laguna'),
                'departamento_id' => 19
            ]);
            Municipio::create([
                'nombre' => ucwords('Santa cruz la laguna'),
                'departamento_id' => 19
            ]);
            Municipio::create([
                'nombre' => ucwords('Santa lucía utatlán'),
                'departamento_id' => 19
            ]);
            Municipio::create([
                'nombre' => ucwords('Santa maría visitación'),
                'departamento_id' => 19
            ]);
            Municipio::create([
                'nombre' => ucwords('Santiago atitlán'),
                'departamento_id' => 19
            ]);
            Municipio::create([
                'nombre' => ucwords('Mazatenango'),
                'departamento_id' => 20
            ]);
            Municipio::create([
                'nombre' => ucwords('Cuyotenango'),
                'departamento_id' => 20
            ]);
            Municipio::create([
                'nombre' => ucwords('San francisco zapotitlán'),
                'departamento_id' => 20
            ]);
            Municipio::create([
                'nombre' => ucwords('San bernardino'),
                'departamento_id' => 20
            ]);
            Municipio::create([
                'nombre' => ucwords('San josé el ídolo'),
                'departamento_id' => 20
            ]);
            Municipio::create([
                'nombre' => ucwords('Santo domingo suchitépequez'),
                'departamento_id' => 20
            ]);
            Municipio::create([
                'nombre' => ucwords('San lorenzo'),
                'departamento_id' => 20
            ]);
            Municipio::create([
                'nombre' => ucwords('Samayac'),
                'departamento_id' => 20
            ]);
            Municipio::create([
                'nombre' => ucwords('San pablo jocopilas'),
                'departamento_id' => 20
            ]);
            Municipio::create([
                'nombre' => ucwords('San antonio suchitépequez'),
                'departamento_id' => 20
            ]);
            Municipio::create([
                'nombre' => ucwords('San miguel panán'),
                'departamento_id' => 20
            ]);
            Municipio::create([
                'nombre' => ucwords('San gabriel'),
                'departamento_id' => 20
            ]);
            Municipio::create([
                'nombre' => ucwords('Chicacao'),
                'departamento_id' => 20
            ]);
            Municipio::create([
                'nombre' => ucwords('Patulul'),
                'departamento_id' => 20
            ]);
            Municipio::create([
                'nombre' => ucwords('Santa bárbara'),
                'departamento_id' => 20
            ]);
            Municipio::create([
                'nombre' => ucwords('San juan bautista'),
                'departamento_id' => 20
            ]);
            Municipio::create([
                'nombre' => ucwords('Santo tomás la unión'),
                'departamento_id' => 20
            ]);
            Municipio::create([
                'nombre' => ucwords('Zunilito'),
                'departamento_id' => 20
            ]);
            Municipio::create([
                'nombre' => ucwords('Pueblo nuevo'),
                'departamento_id' => 20
            ]);
            Municipio::create([
                'nombre' => ucwords('Río bravo'),
                'departamento_id' => 20
            ]);
            Municipio::create([
                'nombre' => ucwords('Totonicapán'),
                'departamento_id' => 21
            ]);
            Municipio::create([
                'nombre' => ucwords('San cristóbal totonicapán'),
                'departamento_id' => 21
            ]);
            Municipio::create([
                'nombre' => ucwords('San francisco el alto'),
                'departamento_id' => 21
            ]);
            Municipio::create([
                'nombre' => ucwords('San andrés xecul'),
                'departamento_id' => 21
            ]);
            Municipio::create([
                'nombre' => ucwords('Momostenango'),
                'departamento_id' => 21
            ]);
            Municipio::create([
                'nombre' => ucwords('Santa maría chiquimula'),
                'departamento_id' => 21
            ]);
            Municipio::create([
                'nombre' => ucwords('Santa lucía la reforma'),
                'departamento_id' => 21
            ]);
            Municipio::create([
                'nombre' => ucwords('San bartolo'),
                'departamento_id' => 21
            ]);
            Municipio::create([
                'nombre' => ucwords('Cabañas'),
                'departamento_id' => 22
            ]);
            Municipio::create([
                'nombre' => ucwords('Estanzuela'),
                'departamento_id' => 22
            ]);
            Municipio::create([
                'nombre' => ucwords('Gualán'),
                'departamento_id' => 22
            ]);
            Municipio::create([
                'nombre' => ucwords('Huité'),
                'departamento_id' => 22
            ]);
            Municipio::create([
                'nombre' => ucwords('La unión'),
                'departamento_id' => 22
            ]);
            Municipio::create([
                'nombre' => ucwords('Río hondo'),
                'departamento_id' => 22
            ]);
            Municipio::create([
                'nombre' => ucwords('San jorge'),
                'departamento_id' => 22
            ]);
            Municipio::create([
                'nombre' => ucwords('San diego'),
                'departamento_id' => 22
            ]);
            Municipio::create([
                'nombre' => ucwords('Teculután'),
                'departamento_id' => 22
            ]);
            Municipio::create([
                'nombre' => ucwords('Usumatlán'),
                'departamento_id' => 22
            ]);
            Municipio::create([
                'nombre' => ucwords('Zacapa'),
                'departamento_id' => 22
            ]);
        //FIN MUNICIPIOS

        for ($i = 0; $i < 10; $i++) {
            $user = User::factory()->create();
            if($user) {
                UserInformation::create([
                    'user_id' => $user->id,
                    'nombres' => fake()->name(),
                    'apellidos' => fake()->lastName(),
                    'fecha_nacimiento' => fake()->date(),
                    'cui' => fake()->unique()->numerify('#############'),
                    'telefono' => fake()->numerify('########'),
                    'email' => $user->email,
                    'municipio_id' => fake()->randomElement(Municipio::pluck('id')->toArray()),
                    'zona' => fake()->numberBetween(1,25),
                    'colonia' => fake()->streetName(),
                    'direccion' => fake()->streetAddress(),
                ]);
            }
        }

        $user = User::factory()->create([
            'email' => 'nelson.o.vasquez@gmail.com',
            'password' => bcrypt('Cyb3rn3lsk8'),
        ]);

        UserInformation::create([
            'user_id' => $user->id,
            'nombres' => 'Nelson Ovidio',
            'apellidos' => 'Vásquez Ventura',
            'fecha_nacimiento' => '1988-06-23',
            'cui' => '2733271000101',
            'telefono' => '48840150',
            'email' => 'nelson.o.vasquez@gmail.com',
            'municipio_id' => 77,
            'zona' => 3,
            'colonia' => 'Anexo Ruedita',
            'direccion' => '2 Calle 1-02',
        ]);

        Post::factory(10)->create();
    }
}
