<?php

namespace App\Http\Controllers;

use App\Mail\ContactMail;
use App\Models\Ontology;
use App\Models\User;
use DOMDocument;
use function foo\func;
use Illuminate\Http\Request;
use App\Models\OntologyRelation;
use App\Models\OntologyClass;
use Illuminate\Http\Response;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $relations = OntologyRelation::select()->whereIn('ontology', explode(',',Auth::user()->ontology))->get();
        $classes = OntologyClass::select()->whereIn('ontology', explode(',',Auth::user()->ontology))->get();
        $ontologies = Ontology::where('user_id', '=', Auth::user()->id)->orderBy('updated_at','desc')->get();
        // Get all ontologies shared or created by the user
        $ontologies = $ontologies->concat(Auth::user()->ontologies)->unique()->sortByDesc('updated_at');
        $users = User::all();
        return view('index', compact('relations', 'classes', 'ontologies', 'users'));
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function aboutUs()
    {
        return view('about-us');
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function tutorial()
    {
        return view('tutorial');
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function warningIndex()
    {
        return view('warning-index');
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    function help()
    {
        return view('help');
    }

    public function exportXML(Request $request)
    {
        if(pathinfo($request->fileName, PATHINFO_EXTENSION) != 'xml')
            $request->fileName = $request->fileName . '.xml';

        // creates the XML file
        $response = Response::create($request->xml, 200);
        $response->header('Content-Type', 'text/xml');
        $response->header('Cache-Control', 'public');
        $response->header('Content-Description', 'File Transfer');
        $response->header('Content-Disposition', 'attachment; filename=' . $request->fileName . '');
        $response->header('Content-Transfer-Encoding', 'binary');

        // Verifies where the request came from (Ontology editor or Thesauru editor)
        // and then saves the file in the specific manager
        if(strpos($request->session()->previousUrl(), '/thesaurus-editor') != false)
            ThesauruController::store($request);

        return $response;
    }

    /**
     * Export the diagram to .SVG format
     * @param Request $request
     * @return Response
     */
    public function exportImage(Request $request)
    {
        if(pathinfo($request->fileName, PATHINFO_EXTENSION) != 'svg')
            $request->fileName = $request->fileName . '.svg';

        $response = Response::create($request->data, 200);
        $response->header('Content-Description', 'File Transfer');
        $response->header('Content-Disposition', 'attachment; filename=' . $request->fileName . '');
        $response->header('Content-Type', 'image/svg');
        return $response;
    }

    /**
     * Export the diagram to .OWL format
     * @param Request $request
     * @return Response
     */
    public function exportOWL(Request $request)
    {
        $dom = new DOMDocument('1.0', 'utf-8');
        $ontology = $dom->createElement('Ontology');
        $ontology->setAttribute('host','www.onto4alleditor.com');
        $ontology->setAttribute('xmlns:xsi','http://www.w3.org/2001/XMLSchema-instance');
        $ontology->setAttribute('xsi:schemaLocation','http://www.w3.org/2002/07/owl# http://www.w3.org/2009/09/owl2-xml.xsd');
        $ontology->setAttribute('xmlns','http://www.w3.org/2002/07/owl#');
        $ontology->setAttribute('xml:base','http://example.com/');
        $ontology->setAttribute('xmlns:rdfs','http://www.w3.org/2000/01/rdf-schema#');
        $ontology->setAttribute('xmlns:xsd','http://www.w3.org/2001/XMLSchema#');
        $ontology->setAttribute('xmlns:rdf','http://www.w3.org/1999/02/22-rdf-syntax-ns#');
        $ontology->setAttribute('xmlns:xml','http://www.w3.org/XML/1998/namespace');
        $ontology->setAttribute('ontologyIRI','http://example.com/myOntology');


        $prefixRdf = $dom->createElement('Prefix');
        $prefixRdf->setAttribute('name','rdf');
        $prefixRdf->setAttribute('IRI','http://www.w3.org/1999/02/22-rdf-syntax-ns#');

        $prefixRdfs = $dom->createElement('Prefix');
        $prefixRdfs->setAttribute('name','rdfs');
        $prefixRdfs->setAttribute('IRI','http://www.w3.org/2000/01/rdf-schema#');

        $prefixXsd = $dom->createElement('Prefix');
        $prefixXsd->setAttribute('name','xsd');
        $prefixXsd->setAttribute('IRI','http://www.w3.org/2001/XMLSchema#');

        $prefixOwl = $dom->createElement('Prefix');
        $prefixOwl->setAttribute('name','owl');
        $prefixOwl->setAttribute('IRI','http://www.w3.org/2002/07/owl#');

        $ontology->appendChild($prefixRdf);
        $ontology->appendChild($prefixRdfs);
        $ontology->appendChild($prefixXsd);
        $ontology->appendChild($prefixOwl);

        $dom->appendChild($ontology);

        $xml = simplexml_load_string($request->xml); // Convert the XML string into a XML object

        /**
         * Clear white spaces from the name and replace them with underscore ('_')
         * @param $name
         * @return mixed|string
         */
        function sanitize($name)
        {
            $name = trim($name);
            $name = filter_var($name, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW);
            $name = str_replace(' ', '_', $name);
            $name = html_entity_decode($name);
            $name = trim($name);
            return $name;
        }

        /**
         * Create a <Class/> Element for the given name
         * @param $name
         * @param $dom
         * @param $ontology
         */
        function createClassElement($name, $dom, $ontology)
        {
            $declaration = $dom->createElement('Declaration');
            $class = $dom->createElement('Class');
            $class->setAttribute('IRI', sanitize($name));
            $declaration->appendChild($class);
            $ontology->appendChild($declaration);
        }

        /**
         * Create a relation (<ObjectProperty>) for the given name
         * @param $name
         * @param $dom
         * @param $ontology
         */
        function createObjectPropertyElement($name, $dom, $ontology)
        {
            $declaration = $dom->createElement('Declaration');
            $objectProperty = $dom->createElement('ObjectProperty');
            $objectProperty->setAttribute('IRI', sanitize($name));
            $declaration->appendChild($objectProperty);
            $ontology->appendChild($declaration);
        }

        /**
         * Searches for the domain class name and the range class name, given the id's
         * @param $domain
         * @param $range
         * @param $xml
         * @return array
         */
        function findDomainRangeName($domain, $range, $xml)
        {
            foreach ($xml->root->object as $object)
            {
                if($object['id'] == ''. $domain .'')
                {
                    $domain = $object['label'];
                }
                if ($object['id'] == ''.$range.'')
                {
                    $range = $object['label'];
                }
            }

            foreach ($xml->root->mxCell as $mxCell)
            {
                if ($mxCell['id'] == ''. $domain .'')
                {
                    $domain = $mxCell['value'];
                }
                else if ($mxCell['id'] == ''.$range.'')
                {
                    $range = $mxCell['value'];
                }
            }

            $names = [
                'Domain' => $domain,
                'Range' => $range,
            ];

            return $names;
        }

        /**
         * Create a <SubClassOf> element for the given relation
         * @param $domain
         * @param $range
         * @param $dom
         * @param $ontology
         * @param $xml
         */
        function createSubClassOfElement($domain, $range, $dom, $ontology, $xml)
        {
            $names = findDomainRangeName($domain,$range,$xml);
            $subClassOf = $dom->createElement('SubClassOf');
            $domainClass = $dom->createElement('Class');
            $domainClass->setAttribute('IRI', sanitize($names['Domain']));
            $rangeClass = $dom->createElement('Class');
            $rangeClass->setAttribute('IRI', sanitize($names['Range']));
            $subClassOf->appendChild($domainClass);
            $subClassOf->appendChild($rangeClass);
            $ontology->appendChild($subClassOf);
        }

        /**
         * Create a <ObjectCardinality> type of element, according to the $cardinality parameter
         * @param $domain
         * @param $range
         * @param $relation
         * @param $cardinality
         * @param $dom
         * @param $ontology
         * @param $xml
         */
        function createCardinalityElement($domain, $range, $relation, $cardinality, $dom,$ontology, $xml)
        {
            $cardinality = strtolower($cardinality);

            if (preg_replace('/[^a-z]/i', '', $cardinality) != 'some' &&
                preg_replace('/[^a-z]/i', '', $cardinality) != 'only' &&
                preg_replace('/[^a-z]/i', '', $cardinality) != 'min' &&
                preg_replace('/[^a-z]/i', '', $cardinality) != 'max' &&
                preg_replace('/[^a-z]/i', '', $cardinality) != 'exactly')
                return;

            $names = findDomainRangeName($domain,$range,$xml);
            $subClassOf = $dom->createElement('SubClassOf');
            $domainClass = $dom->createElement('Class');
            $domainClass->setAttribute('IRI', sanitize($names['Domain']));
            $rangeClass = $dom->createElement('Class');
            $rangeClass->setAttribute('IRI', sanitize($names['Range']));
            $objectProperty = $dom->createElement('ObjectProperty');
            $objectProperty->setAttribute('IRI', sanitize($relation));

            if(preg_replace('/[^a-z]/i', '', $cardinality) == 'some')//some, only, min, max, exactly
            {
                $objectSomeValuesFrom = $dom->createElement('ObjectSomeValuesFrom');
                $subClassOf->appendChild($domainClass);
                $subClassOf->appendChild($objectSomeValuesFrom);
                $objectSomeValuesFrom->appendChild($objectProperty);
                $objectSomeValuesFrom->appendChild($rangeClass);
            }
            else if(preg_replace('/[^a-z]/i', '', $cardinality) == 'only')
            {
                $objectAllValuesFrom = $dom->createElement('ObjectAllValuesFrom');
                $subClassOf->appendChild($domainClass);
                $subClassOf->appendChild($objectAllValuesFrom);
                $objectAllValuesFrom->appendChild($objectProperty);
                $objectAllValuesFrom->appendChild($rangeClass);
            }
            else if(preg_replace('/[^a-z]/i', '', $cardinality) == 'min')
            {
                $objectMinCardinality = $dom->createElement('ObjectMinCardinality');
                $objectMinCardinality->setAttribute('cardinality', (int) filter_var($cardinality, FILTER_SANITIZE_NUMBER_INT));
                $subClassOf->appendChild($domainClass);
                $subClassOf->appendChild($objectMinCardinality);
                $objectMinCardinality->appendChild($objectProperty);
                $objectMinCardinality->appendChild($rangeClass);
            }
            else if(preg_replace('/[^a-z]/i', '', $cardinality) == 'max')
            {
                $objectMaxCardinality = $dom->createElement('ObjectMaxCardinality');
                $objectMaxCardinality->setAttribute('cardinality', (int) filter_var($cardinality, FILTER_SANITIZE_NUMBER_INT));
                $subClassOf->appendChild($domainClass);
                $subClassOf->appendChild($objectMaxCardinality);
                $objectMaxCardinality->appendChild($objectProperty);
                $objectMaxCardinality->appendChild($rangeClass);
            }
            else if(preg_replace('/[^a-z]/i', '', $cardinality) == 'exactly')
            {
                $objectExactCardinality = $dom->createElement('ObjectExactCardinality');
                $objectExactCardinality->setAttribute('cardinality', (int) filter_var($cardinality, FILTER_SANITIZE_NUMBER_INT));
                $subClassOf->appendChild($domainClass);
                $subClassOf->appendChild($objectExactCardinality);
                $objectExactCardinality->appendChild($objectProperty);
                $objectExactCardinality->appendChild($rangeClass);
            }

            $ontology->appendChild($subClassOf);
        }

        /**
         * Create a <InverseObjectProperties> Element for the given parameter
         * @param $relation
         * @param $domain
         * @param $range
         * @param $property
         * @param $dom
         * @param $ontology
         */
        function createInverseObjectPropertiesElement($relation, $domain, $range, $property, $dom, $ontology)
        {
            createObjectPropertyElement($property, $dom, $ontology);
            createObjectPropertyDomainElement($property, $range, $dom, $ontology);
            createObjectPropertyRangeElement($property, $domain, $dom, $ontology);

            $inverseObjectProperties = $dom->createElement('InverseObjectProperties');
            $objectProperty = $dom->createElement('ObjectProperty');
            $objectPropertyRelation = $dom->createElement('ObjectProperty');

            $objectProperty->setAttribute('IRI', $property);
            $objectPropertyRelation->setAttribute('IRI', sanitize($relation));

            $inverseObjectProperties->appendChild($objectProperty);
            $inverseObjectProperties->appendChild($objectPropertyRelation);

            $ontology->appendChild($inverseObjectProperties);
        }

        /**
         * Create a <ObjectPropertyDomain> element for the given parameter
         * @param $relation
         * @param $class
         * @param $dom
         * @param $ontology
         */
        function createObjectPropertyDomainElement($relation, $class, $dom, $ontology)
        {
            $objectPropertyDomain = $dom->createElement('ObjectPropertyDomain');
            $objectProperty = $dom->createElement('ObjectProperty');
            $objectProperty->setAttribute('IRI', $relation);
            $classElement = $dom->createElement('Class');
            $classElement->setAttribute('IRI', $class);

            $objectPropertyDomain->appendChild($objectProperty);
            $objectPropertyDomain->appendChild($classElement);
            $ontology->appendChild($objectPropertyDomain);
        }

        /**
         * Create a <ObjectPropertyRangeElement> for the given parameter
         * @param $relation
         * @param $class
         * @param $dom
         * @param $ontology
         */
        function createObjectPropertyRangeElement($relation, $class, $dom, $ontology)
        {
            $objectPropertyRange = $dom->createElement('ObjectPropertyRange');
            $objectProperty = $dom->createElement('ObjectProperty');
            $objectProperty->setAttribute('IRI', $relation);
            $classElement = $dom->createElement('Class');
            $classElement->setAttribute('IRI', $class);

            $objectPropertyRange->appendChild($objectProperty);
            $objectPropertyRange->appendChild($classElement);
            $ontology->appendChild($objectPropertyRange);
        }

        // When a MxCell has his properties filled, it changes the format of the XML
        // A <object> tag is added to the file
        // Converts elements with the properties filled to OWL
        foreach ($xml->root->object as $object)
        {
            // checks if the element is a class
            if($object->mxCell['edge'] == null && strpos($object->children()[0]['style'], 'ellipse') !== false)
            {
                createClassElement($object['label'], $dom, $ontology);
            } // checks if the element is a relation
            else if($object->mxCell['source'] && $object->mxCell['target'])
            {
                createObjectPropertyElement($object['label'], $dom, $ontology);
                if($object['label'] == 'is_a')
                {
                    createSubClassOfElement($object->mxCell['source'], $object->mxCell['target'], $dom, $ontology, $xml);
                }
                if($object['domain'] != "" && $object['label'] != 'is_a')
                    createObjectPropertyDomainElement($object['label'], $object['domain'], $dom, $ontology);
                if($object['range'] != "" && $object['label'] != 'is_a')
                    createObjectPropertyRangeElement($object['label'], $object['range'], $dom, $ontology);
                if($object['cardinality'])
                    createCardinalityElement($object->mxCell['source'], $object->mxCell['target'], $object['label'], $object['cardinality'], $dom,$ontology, $xml);
            }
            foreach ($object->attributes() as $name => $value)
            {
                if($name != 'id' && $name != 'label' && $value != "")
                {
                    $annotationAssertion = $dom->createElement('AnnotationAssertion');
                    $annotationProperty = $dom->createElement('AnnotationProperty');
                    if($name == 'inverseOf' && $object['label'] != 'is_a')
                    {
                        $annotationProperty->setAttribute('IRI','inverse_of');
                        createInverseObjectPropertiesElement($object['label'] ,$object['domain'], $object['range'],$value, $dom, $ontology);
                    }
                    else if($name == 'importedFrom')
                        $annotationProperty->setAttribute('IRI','imported_from');
                    else if ($name == 'alternativeTerm')
                        $annotationProperty->setAttribute('IRI','alternative_term');
                    else if ($name == 'exampleOfUsage')
                        $annotationProperty->setAttribute('IRI','example_of_usage');
                    else if ($name == 'SubClassOf')
                        $annotationProperty->setAttribute('IRI','SubClass_Of');
                    else
                    $annotationProperty->setAttribute('IRI',sanitize($name));

                    $iri = $dom->createElement('IRI');
                    $iri->textContent = sanitize($object['label']);
                    $literal = $dom->createElement('Literal');
                    $literal->setAttribute('datatypeIRI','&rdf;PlainLiteral');
                    $literal->textContent = $value;
                    $annotationAssertion->appendChild($annotationProperty);
                    $annotationAssertion->appendChild($iri);
                    $annotationAssertion->appendChild($literal);
                    $ontology->appendChild($annotationAssertion);

                }
            }

        }

        // Converts elements without properties filled to OWL
        foreach($xml->root->mxCell as $element)
        {
            if($element['value'])
            {
                // Checks if the element is a class
                if($element['edge'] == null  && strpos($element['style'], 'ellipse') !== false )
                {
                    createClassElement($element['value'], $dom, $ontology);
                }// Checks if the element is a relation
                else if($element['source'] && $element['target'])
                {
                   createObjectPropertyElement($element['value'], $dom, $ontology);
                    if($element['value'] == 'is_a')
                    {
                        createSubClassOfElement($element['source'], $element['target'], $dom, $ontology, $xml);
                    }
                    else if ($element['cardinality'])
                        createCardinalityElement($element['source'], $element['target'], $element['value'], $element['cardinality'], $dom,$ontology, $xml);

                }
            }
        }

        // Formatting the XML text
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;

        return response()->json($dom->saveXML());
        /*
        if(pathinfo($request->fileName, PATHINFO_EXTENSION) != 'owl')
            $request->fileName = $request->fileName . '.owl';

        $response = Response::create($dom->saveXML(), 200);
        $response->header('Content-Description', 'File Transfer');
        $response->header('Content-Disposition', 'attachment; filename=' . $request->fileName . '');
        $response->header('Content-Type', 'text/xml');
        $response->header('Content-Transfer-Encoding', 'binary');
        $response->header('Cache-Control', 'public');
*/

    }


}
