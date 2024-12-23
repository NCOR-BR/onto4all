/**
 * Validates the axiom in the OWL API and returns if
 * the axioms is valid
 */
function validateAxiom() {
    
    let userInput = document.getElementById("Constraint");
    highlightSyntax(userInput);

    if (userInput.value === "") {
        changeTooltipText("empty");
        changeInputBorderColor(userInput, "black");
        return;
    }

    (async () => {
        const rawResponse = await fetch(
            "https://onto4all.repesq.ufjf.br/owlapi/webapi/ontology/valid",
            {
                method: "POST",
                headers: {
                    Accept: "text/plain, */*",
                    "Content-Type": "text/plain",
                },
                body: JSON.stringify({
                    id:
                        "https://onto4alleditor.com/en/ontologies/" +
                        document.getElementById("id").value,
                    outformat: "OWL",
                    ontoclass: getElementsNames(),
                    // split the axioms after each ';' and then remove empty/whitespace strings from the array
                    ontoaxioms: userInput.value
                        .split(";")
                        .filter((e) => String(e).trim()),
                    ontoproperties: [],
                }),
            }
        );
        console.log(rawResponse);
        const content = await rawResponse.text();
        console.log(content);

        if (!rawResponse.ok) {
            changeInputBorderColor(userInput, "yellow");
            return new Error("falhou a requisição"); // cairá no catch da promise
        }

        if (rawResponse.status === 404) {
            return new Error("não encontrou qualquer resultado");
        }
        if (content == "true") {
            changeInputBorderColor(userInput, "green");
            changeTooltipText(true);
        } else {
            changeInputBorderColor(userInput, "red");
            changeTooltipText(false);
        }
    })();
}

/**
 * Change the text and border color from the user input
 * @param userInput
 * @param color
 */
function changeInputBorderColor(userInput, color) {
    switch (color) {
        case "yellow":
            userInput.style.setProperty("border-color", "#f39c12");
            break;
        case "green":
            userInput.style.setProperty("border-color", "green");
            break;
        case "red":
            userInput.style.setProperty("border-color", "red");
            break;
        case "black":
            userInput.style.setProperty("border-color", "black");
            break;
    }
}

/**
 * Change the help text/icon and its color
 * @param axiomIsValid
 */
function changeTooltipText(axiomIsValid) {
    let tooltipText = document.getElementById("help-text");
    let icon = document.getElementById("help-text-icon");
    if (!tooltipText) return;
    let language = getLanguage();
    switch (axiomIsValid) {
        case true:
            tooltipText.childNodes[1].nodeValue = getTranslation(
                "The axioms are valid!"
            ); // get only the text node, not the other inner HTML tags
            tooltipText.style.color = "green";
            icon.className = "fa fa-fw fa-check";
            break;
        case false:
            tooltipText.childNodes[1].nodeValue = getTranslation(
                "The axioms are not valid!"
            );
            tooltipText.style.color = "red";
            icon.className = "fa fa-fw fa-close";
            break;
        case "empty":
            tooltipText.childNodes[1].nodeValue = getTranslation(
                "None axiom to check!"
            );
            tooltipText.style.color = "black";
            icon.className = "fa fa-fw fa-info-circle";
            break;
    }
}

/**
 * Change each word color given the following rules:
 *  Relations = Blue
 *  Classes = Yellow
 *  Instances = Purple
 *  DatatypeProperty = Green
 *  [some, only, exactly, min, max, value] = Orange
 * @param {string} params
 */
function highlightSyntax(input) {
    let words = input.value.split(" ");
    console.log(words);
    let html = "";
    for (let i = 0; i < words.length; i++) {
        if (words[i] == ";") {
            html += "<br>";
            continue;
        }
        html +=
            ' <span style="color:' +
            getWordColor(words[i]) +
            ';font-weight: bolder; display: inline-block; line-break: anywhere;">' +
            words[i] +
            "</span> ";
    }
    document.getElementById("highlight-constraint-text").innerHTML = html;
}

/**
 * Return the correct color to be used in the word
 * @param {string} word
 */
function getWordColor(word) {
    word = removeLineBreaks(word);
    if (classes.some((e) => e.getAttribute("label") == word)) return "#f39c12";
    else if (relations.some((e) => e.getAttribute("label") === word))
        return "#3c8dbc";
    else if (instances.some((e) => e.getAttribute("label") === word))
        return "rebeccapurple";
    else if (
        word == "some" ||
        word == "only" ||
        word == "exactly" ||
        word == "min" ||
        word == "max" ||
        word == "value" ||
        word == "SubClassOf"
    )
        return "black";
    else return "red";
}

function removeLineBreaks(string) {
    return string.replace(/(\r\n|\n|\r)/gm, "");
}
