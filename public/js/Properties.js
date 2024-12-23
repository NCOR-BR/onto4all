
//editor.graph.getSelectionCell()
var currentCell;
const ontologyId = document.getElementById("id");
const iriLink = document.getElementById("IRI-link");
const annotationsTab = document.getElementById('annotations-tab');
const createdPropertyMessage = document.getElementById('created-property-message');
const propertyLabel = document.getElementById("label");

let inputs;
const selectInputs = [
    "Equivalence",
    "DisjointWith",
    "equivalentTo",
    "subpropertyOf",
    "inverseOf",
    "disjointWith",
    "sameAs",
    "differentAs",
    "domain-data-properties",
];
const checkboxInputs = [
    "functional",
    "inverseFunctional",
    "transitive",
    "symetric",
    "asymmetric",
    "reflexive",
    "irreflexive",
];

// Properties for each cell
const classInputs = {
    SubClassOf: document.getElementById("SubClassOf"),
    Equivalence: document.getElementById("Equivalence"),
    Instances: document.getElementById("Instances"),
    TargetForKey: document.getElementById("TargetForKey"),
    DisjointWith: document.getElementById("DisjointWith"),
    Constraint: document.getElementById("Constraint"),
};

const relationInputs = {
    domain: document.getElementById("domain"),
    range: document.getElementById("range"),
    equivalentTo: document.getElementById("equivalentTo"),
    subpropertyOf: document.getElementById("subpropertyOf"),
    inverseOf: document.getElementById("inverseOf"),
    disjointWith: document.getElementById("disjointWith-relations"),
    functional: document.getElementById("functional"),
    inverseFunctional: document.getElementById("inverseFunctional"),
    transitive: document.getElementById("transitive"),
    symetric: document.getElementById("symetric"),
    asymmetric: document.getElementById("asymmetric"),
    reflexive: document.getElementById("reflexive"),
    irreflexive: document.getElementById("irreflexive"),
};

let annotationInputs = {
    IRI: document.getElementById("IRI"),
    comment: document.getElementById("comment"),
    isDefinedBy: document.getElementById("isDefinedBy"),
    seeAlso: document.getElementById("seeAlso"),
    backwardCompatibleWith: document.getElementById("backwardCompatibleWith"),
    deprecated: document.getElementById("deprecated"),
    incompatibleWith: document.getElementById("incompatibleWith"),
    priorVersion: document.getElementById("priorVersion"),
    versionInfo: document.getElementById("versionInfo"),
};

var dataPropertyInputs = {
    "domain-data-properties": document.getElementById("domain-data-properties"),
    "range-data-properties": document.getElementById("range-data-properties"),
    "label-data-properties": document.getElementById("label-data-properties"),
    equivalentTo: document.getElementById("equivalentTo-data-properties"),
    subpropertyOf: document.getElementById("subpropertyOf-data-properties"),
    disjointWith: document.getElementById("disjointWith-data-properties"),
    functional: document.getElementById("functional-data-properties"),
    datatype: document.getElementById("datatype"),
};

const instanceInputs = {
    types: document.getElementById("types"),
    sameAs: document.getElementById("sameAs"),
    differentAs: document.getElementById("differentAs"),
    objectProperties: document.getElementById("objectProperties"),
    dataProperties: document.getElementById("dataProperties"),
    negativeObjectProperties: document.getElementById("negativeObjectProperties"),
    negativeDataProperties: document.getElementById("negativeDataProperties"),
};

/**
 * Function called when a mxCell is selected in the editor
 * @param {mxCell} cell
 */
function getSelectedCell(cell) {
    currentCell = cell;
    if (currentCell == null) return updateTabs();

    let style = currentCell.style;

    if (style.includes("Class")) {
        updateTabs("Class");
        inputs = classInputs;
    } else if (style.includes("Relation")) {
        updateTabs("Relation");
        inputs = relationInputs;
    } else if (style.includes("Instance")) {
        updateTabs("Instance");
        inputs = instanceInputs;
    }
     else return;

    setPropertiesInputs(cell);
}

/**
 * Disable and enable the correspondent tabs on the right sidebar
 * @param {string} cellType
 */
function updateTabs(cellType) {
    switch (cellType) {
        case "Class":
            document.getElementById("classes-nav").click();
            document
                .getElementById("object-properties-nav")
                .classList.add("tab-disabled");
            document
                .getElementById("data-properties-nav")
                .classList.remove("tab-disabled");
            document
                .getElementById("instances-nav")
                .classList.add("tab-disabled");
            document
                .getElementById("classes-nav")
                .classList.remove("tab-disabled");
            document
                .getElementById("annotations-nav")
                .classList.remove("tab-disabled");
            document.getElementById("highlight-constraint-text").innerHTML = "";
            break;

        case "Relation":
            document.getElementById("object-properties-nav").click();
            document
                .getElementById("classes-nav")
                .classList.add("tab-disabled");
            document
                .getElementById("instances-nav")
                .classList.add("tab-disabled");
            document
                .getElementById("data-properties-nav")
                .classList.add("tab-disabled");
            document
                .getElementById("object-properties-nav")
                .classList.remove("tab-disabled");
            document
                .getElementById("annotations-nav")
                .classList.remove("tab-disabled");
            break;

        case "Instance":
            document.getElementById("instances-nav").click();
            document
                .getElementById("classes-nav")
                .classList.add("tab-disabled");
            document
                .getElementById("object-properties-nav")
                .classList.add("tab-disabled");
            document
                .getElementById("data-properties-nav")
                .classList.remove("tab-disabled");
            document
                .getElementById("instances-nav")
                .classList.remove("tab-disabled");
            document
                .getElementById("annotations-nav")
                .classList.remove("tab-disabled");
            break;

        default:
            document.getElementById("empty-nav").click();
            document
                .getElementById("classes-nav")
                .classList.add("tab-disabled");
            document
                .getElementById("object-properties-nav")
                .classList.add("tab-disabled");
            document
                .getElementById("annotations-nav")
                .classList.add("tab-disabled");
            document
                .getElementById("data-properties-nav")
                .classList.add("tab-disabled");
            document
                .getElementById("instances-nav")
                .classList.add("tab-disabled");
            break;
    }
}

/**
 * Update the cell attribute with the given name and value
 * @param {string} name
 * @param {string} value
 */
function updatePropertyInput(name, value) {
    currentCell.setAttribute(name, value);
}

/**
 * Sets the cells properties in their correspondent front end inputs
 * @param {mxCell} cell
 */
function setPropertiesInputs(cell) {
    // sanitize label string
    propertyLabel.value = extractContent(cell.value.getAttribute('label'));
    const cellProperties = cell.value.attributes;
    setCellIRI(cellProperties);
    for (let i = 0; i < cellProperties.length; i++) {
        // set normal properties
        if (inputs.hasOwnProperty(cellProperties[i].name)) {
            // select
            if (selectInputs.includes(cellProperties[i].name)) {
                createSelectOptions(cell, cellProperties[i].name);
                setSelectValue(cellProperties[i]);
            }
            // checkbox
            else if (checkboxInputs.includes(cellProperties[i].name))
                cellProperties[i].value === "true"
                    ? (inputs[cellProperties[i].name].checked = true)
                    : (inputs[cellProperties[i].name].checked = false);
            // string
            else inputs[cellProperties[i].name].value = cellProperties[i].value;
        }

        // set annotations
        if (annotationInputs.hasOwnProperty(cellProperties[i].name))
            annotationInputs[cellProperties[i].name].value =
                cellProperties[i].value;

        // set datapropertiees
        if (dataProperties.includes(cellProperties[i].name)) {
            dataPropertyInputs[cellProperties[i].name].value = cellProperties[i].value;
            // checkbox
            if (cellProperties[i].name == 'functional')
                cellProperties[i].value === "true" ? (dataPropertyInputs[cellProperties[i].name].checked = true) : (dataPropertyInputs[cellProperties[i].name].checked = false);

            // select
            if (cellProperties[i].name == "domain-data-properties") {
                createSelectOptions(cell, cellProperties[i].name);
                setSelectValue(cellProperties[i]);
            }

            const datatypeSelect = ['range-data-properties','equivalentTo','subpropertyOf','disjointWith','functional','datatype'];
            if(datatypeSelect.includes(cellProperties[i].name)) {
                setSelectValue(cellProperties[i]);
            }
        }

        // if the property is not a default one
        if (
            !relationInputs.hasOwnProperty(cellProperties[i].name) &&
            !classInputs.hasOwnProperty(cellProperties[i].name) &&
            !annotationInputs.hasOwnProperty(cellProperties[i].name) &&
            !dataPropertyInputs.hasOwnProperty(cellProperties[i].name) &&
            document.getElementById(cellProperties[i].name) === null
        ) {
            createNewProperty(cellProperties[i].name, cellProperties[i].value);
            console.log("input a ser criado: " + cellProperties[i].name);
        }
    }
    removeNotUsedCreatedPropertiesInputs(cellProperties);
}

function removeNotUsedCreatedPropertiesInputs(cellProperties){
    const elements = document.getElementsByClassName("created-property");
    const propertyNames = Object.values(cellProperties).map(property => property.name);
    for (let i = 0; i < elements.length; i++){
        if(!propertyNames.includes(elements[i].id)){
            console.log("input a ser excluido: " +elements[i].id);
            elements[i].parentElement.remove();

        }
    }
}

function setSelectValue(cellProperties) {
    $(document).ready(function () {
        $("#" + cellProperties.name)
            .select2({
                theme: "classic",
                width: "resolve",
                placeholder: "",
                allowClear: true,
            })
            .val(cellProperties.value.split(","))
            .trigger("change");
    });
}

/**
 * Set the IRI for the given value
 * @param {string} value
 * @returns
 */
function createCellIRI(value) {
    return "https://onto4alleditor.com/" +
    getLanguage() +
    "/ontologies/" +
    ontologyId.value +
    "#" +
    value;
}

function setCellIRI(cellProperties){
    let iriValue = createCellIRI(extractContent(currentCell.value));
    currentCell.setAttribute("IRI", iriValue);
    iriLink.href = iriValue;
}

/**
 * Create the select options for the given property
 * @param {mxCell} cell
 * @param propertyName
 */
function createSelectOptions(cell, propertyName) {
    let select = document.getElementById(propertyName);
    if (select == null) return;
    if (select.options.length > 0) removeSelectOptions(select);
    let options = [];
    if (cell.isEdge()) {
        options = relations;
        if (propertyName === "inverseOf") {
            select.removeAttribute("multiple");
            options = getInverseOfOptions();
        }

        // removes current cell name from the select options
        options = options.filter(
            (e) =>
                e.id !== cell.id &&
                e.getAttribute("label") !== cell.getAttribute("label")
        );
    } else {

        if (propertyName == "domain-data-properties") {

            options = classes.concat(instances);
            // removes the class Thing from the options
            options = options.filter((e) => getTranslation("THING"));
        } else {
            options = classes.filter(
                (e) =>
                    e.id !== cell.id &&
                    e.getAttribute("label") !== cell.getAttribute("label")
            );
        }
        // removes the class Thing from the options
        options = options.filter((e) => getTranslation("THING"));
    }

    // domain property of classes and instances

    // remove duplicated options
    options = options.reduce((unique, o) => {
        if (
            !unique.some(
                (obj) => obj.getAttribute("label") === o.getAttribute("label")
            )
        ) {
            unique.push(o);
        }
        return unique;
    }, []);

    options.forEach((element) => {
        let option = document.createElement("option");
        option.setAttribute("value", element.id);
        option.innerHTML = element.getAttribute("label");
        select.appendChild(option);
    });
}

/**
 * Removes the current options from the given select
 * @param {*} select
 */
function removeSelectOptions(select) {
    while (select.options.length > 0) {
        select.remove(0);
    }
}

/**
 * Returns the correct values for the inverseOf property,
 * if a relation is already a inverseOf another, it should not appear in the select options
 * @returns array
 */
function getInverseOfOptions() {
    let inverseOfValues = [];
    let options = [];

    // push the relations that is an inverse of another
    relations.forEach((relation) => {
        let inverseOf = relation.getAttribute("inverseOf");
        if (inverseOf) {
            inverseOf = inverseOf.split(",");
            removeItemAll(inverseOf, "");
            removeItemAll(inverseOf, "null");
            if (inverseOf !== "null" && inverseOf !== "")
                inverseOfValues.push(
                    getCellById(inverseOf[0])?.getAttribute("label")
                );
        }
    });

    // build the options and includes the current cell inverseOf as a selected option
    relations.forEach((relation) => {
        if (
            !inverseOfValues.includes(relation.getAttribute("label")) ||
            currentCell.getAttribute("inverseOf").includes(relation.id)
        )
            options.push(relation);
    });
    return options;
}


function debounce(callback, wait) {
    let timeout;
    return (...args) => {
        clearTimeout(timeout);
        timeout = setTimeout(function () {
            callback.apply(this, args);
        }, wait);
    };
}

function createNewProperty(label, value = ""){
    if(!validateLabel(label) && document.getElementById(label))
        return alert('Invalid label');

    const formGroup = document.createElement('div');
    formGroup.classList.add('form-group', 'input-group');
    const newPropertyLabel = document.createElement('label', label);
    const newPropertyInput = document.createElement('input');
    const deleteButtonContainer = document.createElement('span');
    deleteButtonContainer.classList.add('input-group-btn');
    deleteButtonContainer.style.verticalAlign = "bottom";
    const deleteButton = document.createElement('button');
    deleteButton.classList.add('btn', 'btn-danger', 'btn-flat');
    const trashIcon = document.createElement("i");
    trashIcon.classList.add('fa', 'fa-fw','fa-trash-o');
    deleteButton.appendChild(trashIcon);

    deleteButtonContainer.appendChild(deleteButton);


    newPropertyLabel.innerText = label;
    newPropertyInput.classList.add('form-control',  "created-property");
    newPropertyInput.id = label;
    newPropertyInput.onchange = function (){
        updatePropertyInput(this.id, this.value);
    }
    //annotationInputs[label] = value;

    formGroup.appendChild(newPropertyLabel);
    formGroup.appendChild(newPropertyInput);
    formGroup.appendChild(deleteButtonContainer);

    annotationsTab.appendChild(formGroup);
    currentCell.setAttribute(label, "");

    // remove property
    deleteButtonContainer.addEventListener('click', () => {
        if(confirm("Are you sure you want to delete this property?")) {
            deleteButtonContainer.parentElement.remove();
            // remove from interface
            delete currentCell[deleteButtonContainer.previousElementSibling.id];
            // remove from mxGraph object
            delete currentCell.value.removeAttribute(deleteButtonContainer.previousElementSibling.id);
        }
        else return;
    })

    dispatchSuccessMessage();
}

function dispatchSuccessMessage() {
    createdPropertyMessage.style.visibility = "visible";
    setTimeout(
        function() {
            createdPropertyMessage.style.visibility = "hidden";
        }, 5000);
}

/**
 * Validate if the current label is already an annotation property,
 * or it's an attribute from mxCell object.
 * @param label
 * @returns {boolean}
 */
function validateLabel(label) {
    const propertyNames = Object.values(currentCell.value.attributes).map(property => property.name);
    return !Object.keys(currentCell).includes(label) && !propertyNames.includes(label);
}

// Add keyup events for the given textArea
let constraintInput = classInputs.Constraint;
constraintInput.addEventListener("keyup", function () {
    document.getElementById("help-text-icon").className = "fa fa-fw fa-clock-o";
    document.getElementById("help-text").childNodes[1].nodeValue =
        getTranslation("Checking the axioms, please wait...");
});

// Call the Class Expression Edior / Axiom Editor 2 seconds
// after the user stops typing
constraintInput.addEventListener(
    "keyup",
    debounce(() => {
        // code you would like to run 2000ms after the keyup event has stopped firing
        // further keyup events reset the timer, as expected
        validateAxiom();
    }, 2000)
);
