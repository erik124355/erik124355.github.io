CREATE TABLE alle_25_vuotiaiden_opiskelijoiden_harrastukset(
    id INT AUTO_INCREMENT PRIMARY KEY,
    nimi varchar(100) NOT NULL,
    harrastus varchar(100) NOT NULL,
    ika INT(2) NOT NULL CHECK (ika < 25),
    opiskelija varchar(1) NOT NULL CHECK (opiskelija IN ('y', 'N')),
    aikaleima TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)

CREATE TABLE uudet_traktorit(
    id INT AUTO_INCREMENT,
    malli varchar(200) NOT NULL,
    paino INT(100) NOT NULL,
    hevosvoimat INT(100) NOT NULL,
    valmistus_vuosi INT(4) NOT NULL,
    hinta DECIMAL(10, 2) NOT NULL,
    aikaleima TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY(malli, valmistus_vuosi, id)

)

CREATE TABLE oppilaitokset_ita_Suomessa(
    id INT AUTO_INCREMENT PRIMARY KEY,
    nimi varchar(200) NOT NULL,
    kunta varchar(200) NOT NULL,
    oppilaitten_maara INT(6),
    Koulutus_tyyppi varchar(100) NOT NULL,
    aikaleima TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)

CREATE TABLE eksoottiset_kotielaimet(
    id INT AUTO_INCREMENT PRIMARY KEY,
    elain varchar(100) NOT NULL,
    rotu varchar(100) NOT NULL,
    alkuperamaa varchar(3) NOT NULL,
    paino DECIMAL(4, 2) NOT NULL,
    pituus_cm DECIMAL(4, 2) NOT NULL,
    elin_ika INT(4) NOT NULL,
    aikaleima TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)