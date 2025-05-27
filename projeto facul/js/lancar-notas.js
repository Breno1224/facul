// Simulação para preenchimento dinâmico de turmas e disciplinas
document.addEventListener("DOMContentLoaded", () => {
    const turmas = ["1A", "2B"];
    const disciplinas = ["Matemática", "História"];

    const turmaSelect = document.getElementById("turmaSelect");
    const disciplinaSelect = document.getElementById("disciplinaSelect");

    turmas.forEach(t => {
        const opt = document.createElement("option");
        opt.value = t;
        opt.textContent = t;
        turmaSelect.appendChild(opt);
    });

    disciplinas.forEach(d => {
        const opt = document.createElement("option");
        opt.value = d;
        opt.textContent = d;
        disciplinaSelect.appendChild(opt);
    });
});

function carregarAlunos() {
    const alunos = ["Ana Silva", "Bruno Costa", "Carlos Lima"]; // Simulado

    const alunosTable = document.getElementById("alunosTable");
    alunosTable.innerHTML = "";

    alunos.forEach(aluno => {
        const tr = document.createElement("tr");
        tr.innerHTML = `
            <td>${aluno}</td>
            <td><input type="number" name="nota_${aluno}" step="0.01" min="0" max="10"></td>
        `;
        alunosTable.appendChild(tr);
    });

    document.getElementById("alunosSection").classList.remove("hidden");
}

document.getElementById("notasForm").addEventListener("submit", (e) => {
    e.preventDefault();
    alert("Notas lançadas com sucesso!");
    document.getElementById("alunosSection").classList.add("hidden");
});
