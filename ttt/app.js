const buttons = document.querySelectorAll(".box div");
const scoreUi = document.querySelector(".score .value")

const markIcon = document.querySelector(".score .icon#mark img")
const circleIcon = document.querySelector(".score .icon#circle img")
//console.log(markIcon, circleIcon)

let mark = false; // controls turns
let gameOver = false;

const itemsClear = [
  null, null, null,
  null, null, null,
  null, null, null
];
let items = [...itemsClear];

let score = [0,0];

const relations = [
  [0,3,6], [1,4,7], [2,5,8], // columns
  [0,1,2], [3,4,5], [6,7,8], // rows
  [0,4,8], [2,4,6] // diagonals
];

let i = 0;

function changeTurn() {
  mark = !mark;
  markIcon.classList.toggle("active");
  circleIcon.classList.toggle("active");
}
markIcon.classList.toggle("active"); // first active by default


function checkWin() {
  // check rows, columns
  relations.forEach(relation => {
    let a = items[relation[0]];
    let b = items[relation[1]];
    let c = items[relation[2]];

    // win
    if (a !== null && a === b && b == c) {
      colorSquares(relation);

      score[a ? 0 : 1] += 1; // increment score
      scoreUi.textContent = `${score[0]} - ${score[1]}`;

      gameOver = true;
      return;
    }
  });
  return;
}

function colorSquares(indexes) {
  //console.log("coloring")
  indexes.forEach(i => {
    buttons[i].classList.add("colored");
  });
}

function clear() {
  buttons.forEach(button => {
    button.classList.remove("colored");
    button.style.backgroundImage = "";
  });

  gameOver = false;
  items = [...itemsClear];
}

buttons.forEach(button => {
    const index = i
    button.addEventListener("click", () => {
        if (button.style.backgroundImage !== "" || gameOver) {return;}
        //console.log(button.style.backgroundImage)

        // update
        changeTurn();
        button.style.backgroundImage = mark ? 'url(mark.png)' : 'url(circle.png)';
        
        items[index] = mark;
        //button.style.backgroundColor

        // 
        checkWin();
    });
    i += 1;
});

const clearButton = document.getElementById("clear");

clearButton.addEventListener("click", clear);