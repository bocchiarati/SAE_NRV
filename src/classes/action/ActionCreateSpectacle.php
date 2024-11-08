<?php

namespace iutnc\nrv\action;

use iutnc\nrv\exception\RepoException;
use iutnc\nrv\repository\NrvRepository;

class ActionCreateSpectacle extends Action
{

    function executeGet(): string {
        $repository = NrvRepository::getInstance();
        $locations = $repository->getAllLieu();

        // creation des options pour le select du lieu
        $options = '';
        foreach ($locations as $lieuID => $nom) {
            $options .= "<option value='{$lieuID}'>{$nom}</option>";
        }
        return <<< END
    <style>
    /* Container for the form */
    form {
        display: flex;
        justify-content: center;
        align-items: center;
        flex-direction: column;
        margin-top: 20px;
        font-family: Arial, sans-serif;
        background-color: #f9f9f9;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        width: 300px;
        margin: 0 auto;
    }

    /* Input and select styling */
    #date, #location, #new-location, #address {
        width: 100%;
        padding: 10px;
        margin: 10px 0;
        border-radius: 4px;
        border: 1px solid #ccc;
        font-size: 14px;
        box-sizing: border-box;
    }

    /* Button styling */
    button {
        background-color: #ff0000;
        color: white;
        padding: 10px 20px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 16px;
        transition: background-color 0.3s ease;
    }

    /* Button hover effect */
    button:hover {
        background-color: #ff0000;
    }

    /* Focus effect on input and select */
    #date:focus, #location:focus, #new-location:focus, #address:focus {
        border-color: #ff0000;
        outline: none;
    }
</style>
<h1 style="text-align: center; font-size:60px">Creation D'une soirée</h1>
<form method="post" action="?action=createSoiree">
<p>Inserer une date</p>
    <input type="date" id="date" name="date" required>
    <p>Selectionner un lieu</p>
    <select id="location" name="location">
        <option value="" disabled selected>Choisir un lieu</option>
        $options
        <option value="Autre">Ou insérer un nouveau lieu</option>
    </select>
    
    <input type="text" id="new-location" name="new-location" placeholder="Nouveau lieu" style="display: none;">
    <input type="text" id="address" name="address" placeholder="Adresse" style="display: none;">

    <button type="submit">Créer</button>
</form>
<script>
    // Script to show/hide the new location and address inputs based on the selection
    document.getElementById('location').addEventListener('change', function() {
        var newLocationInput = document.getElementById('new-location');
        var addressInput = document.getElementById('address');
        
        if (this.value === 'Autre') {
            newLocationInput.style.display = 'block';
            addressInput.style.display = 'block';
        } else {
            newLocationInput.style.display = 'none';
            addressInput.style.display = 'none';
        }
    });
</script>
END;

    }

    /**
     * @throws RepoException
     */
    function executePost(): string
    {
        $pdo = NrvRepository::getInstance();
        if($_POST['location'] === "Autre"){
            $pdo->saveSoiree($_POST['date'], null, $_POST['new-location'], $_POST['address']);
            $message =  "<p>Soirée et Lieu créée avec succes</p>";
        }
        else{
            $pdo->saveSoiree($_POST['date'], $_POST['location'],null, null);
            $message = "Soirée créée avec succes";
        }
        return $message;
    }
}