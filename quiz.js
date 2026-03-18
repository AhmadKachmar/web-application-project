let questions = [];
let userAnswers = [];

function startQuiz(numQuestions) {
    questions = [];
    userAnswers = [];
    document.getElementById('quiz-container').style.display = 'block';
    document.getElementById('result-container').innerHTML = '';

    for (let i = 0; i < numQuestions; i++) {
        const num1 = Math.floor(Math.random() * 30) + 1;
        const num2 = Math.floor(Math.random() * 30) + 1;
        const operators = ['+', '-', '*', '/'];
        const operator = operators[Math.floor(Math.random() * operators.length)];

        questions.push({
            num1: num1,
            num2: num2,
            operator: operator,
            correctAnswer: calculateAnswer(num1, num2, operator),
        });
    }

    displayQuestions();
}

function calculateAnswer(num1, num2, operator) {
    switch (operator) {
        case '+': return num1 + num2;
        case '-': return num1 - num2;
        case '*': return num1 * num2;
        case '/': return parseFloat((num1 / num2).toFixed(2)); // Round to 2 decimals
        default: return 0;
    }
}

function displayQuestions() {
    const questionsDiv = document.getElementById('questions');
    questionsDiv.innerHTML = '';
    questions.forEach((question, index) => {
        const questionDiv = document.createElement('div');
        questionDiv.className = 'question';
        questionDiv.innerHTML = `
            <p>Question ${index + 1}: ${question.num1} ${question.operator} ${question.num2} = </p>
            <input type="text" id="answer-${index}" placeholder="Your answer" class="quiz-input" required>
        `;
        questionsDiv.appendChild(questionDiv);
    });
}

async function submitQuiz() {
    const userAnswers = [];
    let score = 0;

    questions.forEach((question, index) => {
        const userAnswer = parseFloat(document.getElementById(`answer-${index}`).value);
        userAnswers.push(userAnswer);
        if (!isNaN(userAnswer) && userAnswer === question.correctAnswer) score++;
    });

    const finalGrade = ((score / questions.length) * 100).toFixed(2);
    
    try {
        const response = await fetch('quiz.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ grade: finalGrade }),
        });

        const result = await response.json();
        
        if (!response.ok) {
            throw new Error(result.error || "Failed to save grade.");
        }
        
        // Show success message on the SAME PAGE
        alert(`Quiz submitted! Your grade: ${finalGrade}%`);
        
        // Optionally reset the quiz or show results
        document.getElementById('result-container').innerHTML = `
            <h2>Quiz Completed!</h2>
            <p>Your grade: ${finalGrade}%</p>
            <button onclick="startQuiz(10)">Try Again</button>
        `;
        
        // DON'T redirect - stay on quiz.html
        
    } catch (error) {
        console.error("Error:", error);
        alert(error.message);
    }
}