<?php
include_once(__DIR__ . '/../config.php');
include_once(__DIR__ . '/../Model/question.php');

class QuestionController {

    // LIST
    public function listQuestions() {
        $sql = "SELECT * FROM question";
        $db = config::getConnexion();

        return $db->query($sql);
    }

    // DELETE
    public function deleteQuestion($id) {
        $db = config::getConnexion();
        $sql = "DELETE FROM question WHERE id_q = :id";

        $stmt = $db->prepare($sql);
        $stmt->bindValue(':id', $id);
        $stmt->execute();
    }

    // ADD (CORRIGÉ + REPLCE REQUÊTE PROPRE)
    public function addQuestion(Question $q) {

        $sql = "INSERT INTO question VALUES (NULL, :id_t, :type, :contenu_q, :reponse)";

        $db = config::getConnexion();

        $stmt = $db->prepare($sql);

        $stmt->execute([
            'id_t' => $q->getTestId(),
            'type' => $q->getType(),
            'contenu_q' => $q->getContenu(),
            'reponse' => $q->getReponse()
]);
    }

    // UPDATE
    public function updateQuestion(Question $q, $id) {

        $sql = "UPDATE question SET 
                    id_t = :id_t,
                    type = :type,
                    contenu_q = :contenu_q,
                    reponse = :reponse
                WHERE id_q = :id";

        $db = config::getConnexion();
        $stmt = $db->prepare($sql);

        $stmt->execute([
            'id' => $id,
            'id_t' => $q->getTestId(),
            'type' => $q->getType(),
            'contenu_q' => $q->getContenu(),
            'reponse' => $q->getReponse()
        ]);
    }

    // SHOW
    public function showQuestion($id) {

        $sql = "SELECT * FROM question WHERE id_q = :id";
        $db = config::getConnexion();

        $stmt = $db->prepare($sql);
        $stmt->execute(['id' => $id]);

        return $stmt->fetch();
    }
}
?>