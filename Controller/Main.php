<?php

require_once("../Model/Structure.php");
require_once("../Model/Association.php");
require_once("../Model/Entreprise.php");
require_once ("../Model/Secteur.php");
require_once('../Model/db/PDO.php');

//Affiche le tableau des structures
function afficher_structures():void
{
    $structures = getAllStructures();   //Recupère une liste d'objets Structure

    //TABLEAU DES STRUCTURES//
    echo "<table class=\"table table-hover\" id='listeStructures'>";

        echo "<tr>";
            echo "<th scope=\"col\"> TYPE</th>";
            echo "<th scope=\"col\"> NOM </th>";
            echo "<th scope=\"col\"> RUE </th>";
            echo "<th scope=\"col\"> CODE POSTALE </th>";
            echo "<th scope=\"col\"> VILLE </th>";
            echo "<th scope=\"col\"> NB ACTIONNAIRES/DONNATEURS </th>";
            echo "<th scope=\"col\"> SECTEURS </th>";
            echo "<th scope=\"col\"> SUPPRESSION </th>";
            echo "<th scope=\"col\"> MODIFIER</th>";
        echo "</tr>";

        foreach ($structures as $structure) {
            echo "<tr>";

                //Si ESTASSO vaut 0, alors c'est une entreprise
                if ($structure->estAsso() == 0) {
                    echo "<td> Entreprise</td>";
                } else {
                    echo "<td> Association</td>";
                }

                //Infos générique pour chaque structure
                echo "<td>" . $structure->getNom() . "</td>";
                echo "<td>" . $structure->getRue() . "</td>";
                echo "<td>" . $structure->getCp() . "</td>";
                echo "<td>" . $structure->getVille() . "</td>";
                echo "<td>" . $structure->getNbContributeurs() . "</td>";

                //Affichage des secteurs
                echo "<td>";
                    $idSecteurs = getSecteursIdByStructureId($structure->getId());
                    foreach ($idSecteurs as $idSecteur) {
                        $libelleSecteur = getSecteurLibelleById($idSecteur[0]);
                        echo $libelleSecteur . '<br>';
                    }

                echo "</td>";

                echo '<td><form method="post" action="">';
                    echo "<input hidden name='idModifier' value='" . $structure->getId() . "'/>";
                    echo '<input class="btn btn-secondary" type="submit" value="Modifier"/>';
                echo '</form> </td>';

                echo '<td><form method="post" action="">';
                    echo "<input hidden name='idSuppression' value='" . $structure->getId() . "'/>";
                    echo '<input  class="btn btn-danger" type="submit" value="Supprimer"/>';
                echo '</form> </td>';


            echo "</tr>";
        }

    echo "</table>";
}

//Affiche la liste des secteurs
function afficher_secteurs():void
{
    $secteurs = getAllSecteurs();   //Recupère la liste des Secteurs

    //TABLEAU DES SECTEURS AVEC BOUTONS DE MODIFICATION//
    echo '<table>';
    foreach ($secteurs as $secteur) {

        echo '<div class="form-group">';
            echo "<tr>";
                echo "<td>" . $secteur->getNom() . "</td>";
                echo "<td> <input class='modifieSecteur' name='idSecteurAModifier' type='submit' value='" . $secteur->getId() . "'/>";
                echo "<td> <input class='deleteSecteur' name='idSecteurSupprime' type='submit' value='" . $secteur->getId() . "'/>";
            echo "</tr>";
        echo '</div>';

    }
    echo '</table>';


}

//Retourne vrai si le nom d'un secteur n'est pas vide
function verifierSecteur(string $libelle):bool
{
    return trim($libelle) !== '';
}

//Prépare l'insertion d'un secteur en base de données
function inserer_nouveau_secteur(string $libelle):void
{

    $secteurs = getAllSecteurs();   //Récupère la liste des secteurs
    $secteurPresent = false;

    $i = 0;
    //Tant qu'on a pas parcouru tous les secteurs ET que le secteur n'est pas présent
    while ($i < sizeof($secteurs) && !$secteurPresent) {
        //Prends la valeur true si le resultat est différent du libelle cherché
        $secteurPresent = ($secteurs[$i]->getNom() == $libelle);
        $i++;
    }

    //Si le secteur n'existe pas et que son nom est valide alors on l'insère
    if (!$secteurPresent && verifierSecteur($libelle)) {
        insertSecteur($libelle);
    }
}

//Vérifie si les informations d'une structure sont correctes (pas de champs vides ou de nombres négatifs)
function verifierStructure(Structure $struct):bool
{
    return trim($struct->getNom()) !== ''
        && trim($struct->getRue()) !== ''
        && (int)$struct->getCp() >= 0
        && trim($struct->getVille()) !== ''
        && (int)$struct->getNbContributeurs() >= 0;
}

//Prépare l'insertion d'une structure en base de données
function inserer_nouvelle_structure(string $nom, string $rue, string $cp, string $ville, string $structure, string $nbDonAct, $checkbox_list):void
{
    //Si la structure à au moins 1 secteur
    if ($checkbox_list) {

        $structures = getAllStructures();   //Récupération de la liste de structures

        //Création de l'objet Structure correspondant
        if ($structure == "Association") {
            $nouvelleStructure = Association::buildFromData(-1,$nom,$rue,$cp,$ville,$nbDonAct);
        } else {
            $nouvelleStructure = Entreprise::buildFromData(-1,$nom,$rue,$cp,$ville,$nbDonAct);
        }

        $i = 0;
        $structurePresent = false;
        //On parcours les structures présentes en la comparant à la structure actuelle
        while ($i < sizeof($structures) && !$structurePresent) {
            $structurePresent = $nouvelleStructure->isEqual($structures[$i]);
            $i++;
        }

        //Si la structure n'existe pas on l'ajoute
        if (!$structurePresent && verifierStructure($nouvelleStructure)) {
            $idStructure = insertStructure($nouvelleStructure);

            //Création des liens entre la structure et ses secteurs
            foreach ($checkbox_list as $checkbox) {
                insertLinkSecteursStructure((int)$idStructure, (int)$checkbox);
            }
        }
    }

}

//Supprime une structure selon son id
function supprimer_structure(int $id):void
{
    $structures = getAllStructures();   //Récupération de la liste de structures

    $i = 0;
    $structurePresent = false;
    //Tant qu'on a pas parcouru toutes les structures et qu'on a pas trouvé
    while ($i < sizeof($structures) && !$structurePresent) {
        $structurePresent = $structures[$i]->getId() == $id;
        $i++;
    }

    //Si la structure existe bien, on la supprime
    if ($structurePresent) {
        deleteStructure($id);
    }
}

function supprimer_secteur(int $id):void
{
    $secteurs = getAllSecteurs();   //Récupère la liste des secteurs
    $linkSecteurStructure = getAllLinkSecteurStructure();   //Récupère la liste des liens entre secteurs et structures

    $i = 0;
    $secteurPresent = false;
    $secteurUtilise = false;
    //Tant qu'on a pas parcouru toutes les secteurs et qu'on a pas trouvé le secteur et qu'il n'est pas utilisé par une structure
    while ($i < sizeof($secteurs) && !$secteurPresent) {
        $secteurPresent = $secteurs[$i]->getId() == $id;
        $j = 0;
        //On parcours la liste des liens pour vérifier qu'il n'est pas utilisé
        while ($j < sizeof($linkSecteurStructure) && !$secteurUtilise){
            $secteurUtilise = $linkSecteurStructure[$j][2] == $id;
            $j++;
        }
        $i++;
    }



    //Si le secteur existe et qu'il n'est pas utilisé, on le supprime
    if ($secteurPresent && !$secteurUtilise) {
        deleteSecteur($id);
    }
}

function afficher_checkbox_secteurs($checklist):void
{
    $secteurs = getAllSecteurs();   //Récupère la liste des secteurs

    //Affiche la liste des secteurs avec une checkbox pour la selection lors de la création d'une structure
    for ($i = 0; $i < sizeof($secteurs); $i++) {
        $id = $secteurs[$i]->getId();
        echo " <div class=\"form-check\">";

            echo '<label class="form-check-label">' .
                '<input type="checkbox" id="' . $id . '" name="check_list[]" class="form-check-input" value="' . $id . '"';
            if (!is_null($checklist) && in_array($id, $checklist)) {
                echo ' checked="checked" ';
            }
            echo '>' . $secteurs[$i]->getNom();
            '</label>';
            echo "</li>";
        echo "</div>";
    }
}

//Récupère un objet structure en fonction de son Id en base de données
function recuperer_structure_par_id(int $id)
{
    return getStructureById($id);
}

//Récupère tous les id des secteurs associés à une structure
function recuperer_idSecteurs_par_idStructure(int $id):array
{
    $idSecteurs = getSecteursIdByStructureId($id);
    $res = [];

    for ($i = 0; $i < sizeof($idSecteurs); $i++) {
        $res[$i] = $idSecteurs[$i][0];
    }

    return $res;
}

//Récupère le nom d'un secteur en fonction de son id
function recuperer_libelle_secteur_par_id(int $id)
{
    return getSecteurLibelleById($id);
}

//Modifie une structure selon les valeurs en paramètre en se basant sur son id
function modifier_structure(int $id, string $nom, string $rue, string $cp, string $ville, string $structure, string $nbDonAct, $checkbox_list):void
{
    //Si la structure modifiée à au moins 1 secteur
    if ($checkbox_list) {
        $structures = getAllStructures();   //On récupère la liste des structures

        //Création de la structure correspondante
        if ($structure == "Association") {
            $structAModifier = Association::buildFromData($id, $nom, $rue, $cp, $ville,$nbDonAct);
        } else {
            $structAModifier = Entreprise::buildFromData($id, $nom, $rue, $cp, $ville,$nbDonAct);
        }

        $i = 0;
        $structurePresent = false;
        //On vérifie que la structure d'origine existe
        while ($i < sizeof($structures) && !$structurePresent) {
            $item = $structures[$i];

            //Vaut faux si différent
            $structurePresent = $structAModifier->isEqual($item);

            //Si c'est présent il faut vérifier si les associations sont les mêmes
            if ($structurePresent) {
                $anciensSecteurs = getSecteursIdByStructureId($id); //Récupère les secteurs liés à une structure

                $j = 0;
                $resFinalAnciensSecteurs = [];
                //Mise en forme des secteurs pour la comparaison
                while ($j < sizeof($anciensSecteurs)) {
                    $resFinalAnciensSecteurs[$j] = $anciensSecteurs[$j][0];
                    $j++;
                }

                //Vaut vrai si il n'y a aucune différence dans les associations
                $structurePresent = empty(array_diff($resFinalAnciensSecteurs, $checkbox_list));
            }
            $i++;
        }

        //Si la structure qu'on vient de modifier n'existe pas en base, on l'ajoute
        if (!$structurePresent && verifierStructure($structAModifier)) {
            updateStructure($structAModifier);
            deleteAllLinkByIdStructure($id);    //Suppression de tout ses anciens liens avec ses secteurs

            //Création de ses nouveaux liens avec ses secteurs
            foreach ($checkbox_list as $checkbox) {
                insertLinkSecteursStructure($id, (int)$checkbox);
            }
        }
    }
}

//Modifie le nom d'un secteur
function modifier_secteur(int $id, string $nom):void
{
    $secteur = Secteur::buildFromData($id, $nom);
    if (verifierSecteur($secteur->getNom())){
        updateSecteur($secteur);
    }
}

?>