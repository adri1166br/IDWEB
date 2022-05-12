const form = document.getElementById("fromRadical");
const answer = document.getElementById("answer");
const notification = document.getElementById("notification-wrapper");

const API_URL = "/IDweb/api/calculate-equation.php";

form.addEventListener("submit", (e) => {
  e.preventDefault();
  new FormData(form);
});

form.addEventListener("formdata", (e) => {
  request(e.formData)
    .then((res) => {
      if (!res) return;
      answer.innerText = "";
      const result = res.split(",");
      let answerText = "";
      result.map((res, i) => {
        answerText += `x${i + 1} = ${res}\n`;
      });
      answer.value = answerText;

      notification.classList.toggle("hidden");
      // Hide notification after 5 seconds
      setTimeout(() => notification.classList.toggle("hidden"), 5000);
    })
    .catch((err) => {
      console.error(err.message);
    });
});

async function request(data) {
  const body = new URLSearchParams();
  const options = {
    method: "POST",
    body,
  };
  for (const pair of data) {
    body.append(pair[0], pair[1]);
  }

  const response = await fetch(API_URL, options);
  return await response.json();
}
