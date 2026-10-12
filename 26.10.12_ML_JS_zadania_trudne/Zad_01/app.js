let cashe = JSON.parse( localStorage.getItem("casheUrlResults") );

function Save() {
    localStorage.setItem("casheUrlResults", JSON.stringify(cashe));
}

if (!cashe) {
    cashe = {};
    Save();
}

async function fetchWithCache(url, ttlMs) {
    if (cashe[url] && Date.now() - cashe[url]["timestamp"] <= ttlMs ) {
        // get cashed
        console.log("get cashed");
        
        return cashe[url]["data"];

    } else {
        // request new data
        console.log("request");

        try {
            // fetch
            const response = await fetch(url);
            if (!response.ok) { throw new Error ("No response!"); }
            // throw new Error ("Test");
            
            const data = await response.json();
            
            // cashe
            cashe[url] = {data : data, timestamp : Date.now()};
            Save();

            return data;
        } catch (e) {
            console.log(e);

            // return old data
            console.log("Returning outdated data!")
            return cashe[url]["data"];
        }
    }
}

let result = fetchWithCache(window.location.pathname + "/dane.php", 10000) // 1s
.then(console.log); 