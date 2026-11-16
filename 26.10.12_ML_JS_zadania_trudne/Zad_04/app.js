
class EventEmitter {
    constructor() {
        this.events = {};
    }

    // Podlacza funkcje
    on(eventName, listener) {
        if (!this.events[eventName]) {
            this.events[eventName] = [];
        }

        this.events[eventName].push(listener);
    }

    // Wywoluje zdarzenie -> wykonuje wszystkie funkcje
    emit(eventName, ...args) {
        if (!this.events[eventName]) return;

        this.events[eventName].forEach(listener => {
            listener(...args);
        });
    }

    // Odlacza funkcje
    off(eventName, listener) {
        if (!this.events[eventName]) return;

        this.events[eventName] = this.events[eventName].filter(
            fn => fn !== listener
        );
    }

    // Podlacza funkcje jednorazową (usuwa sie po pierwszym wykonaniu)
    once(eventName, listener) {
        const wrapper = (...args) => {
            this.off(eventName, wrapper);
            listener(...args);
        };

        this.on(eventName, wrapper);
    }
}


// Test
const emitter = new EventEmitter();

function powitanie(imie) {
    console.log("Witaj " + imie);
}

// Podlaczenie
emitter.on("start", powitanie);

emitter.once("start", () => {
    console.log("To wykona sie tylko raz!");
});

// Wywolanie
emitter.emit("start", "Piotr");
emitter.emit("start", "Adam");

// Usuniecie
emitter.off("start", powitanie);
emitter.emit("start", "Piotr"); // Nie wyswietla sie