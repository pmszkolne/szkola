function deepClone(obj) {
    // prymitywy, null -> nie trzeba kopiowac
    if (obj === null || typeof obj !== "object") {
        return obj;
    }

    // kopiowanie
    const copy = Array.isArray(obj) ? [] : {}; // array lub dictionary

    for (const key in obj) {
        if (Object.hasOwn(obj, key)) { // ignoruje odziedziczone klucze
            copy[key] = deepClone(obj[key]); // rekursywne kopiowanie
        }
    }

    return copy;
}

// test

const osoba = {
    imie: "Adam",
    adres: {
        miasto: "Warszawa"
    },
    zainteresowania: ["gry", "programowanie"]
};

const kopia = deepClone(osoba);
kopia.adres.miasto = "Krakow";
kopia.zainteresowania.push("sport");

// check
console.log(osoba);
console.log(kopia);