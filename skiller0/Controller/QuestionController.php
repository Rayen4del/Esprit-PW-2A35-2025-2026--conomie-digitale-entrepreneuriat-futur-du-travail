<?php
include(__DIR__ . '/../config.php');
include(__DIR__ . '/../Model/question.php');

class QuestionController {

    public function listQuestions() {
        $sql = "SELECT * FROM question";
        $db = config::getConnexion();

        try {
            return $db->query($sql);
        } catch (Exception $e) {
            die('Error:' . $e->getMessage());
        }
    }

    public function deleteQuestion($id) {
        $sql = "DELETE FROM question WHERE id_q = :id";
        $db = config::getConnexion();
        $req = $db->prepare($sql);
        $req->bindValue(':id', $id);

        try {
            $req->execute();
        } catch (Exception $e) {
            die('Error:' . $e->getMessage());
        }
    }

    public function addQuestion(Question $q) {
        $sql = "INSERT INTO question VALUES (NULL, :id_t, :type, :contenu_q)";
        $db = config::getConnexion();

        try {
            $query = $db->prepare($sql);
            $query->execute([
                'id_t' => $q->getTestId(),
                'type' => $q->getType(),
                'contenu_q' => $q->getContenu()
            ]);
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
        }
    }

    public function updateQuestion(Question $q, $id) {
        try {
            $db = config::getConnexion();
            $query = $db->prepare(
                "UPDATE question SET 
                    id_t = :id_t,
                    type = :type,
                    contenu_q = :contenu_q
                WHERE id_q = :id"
            );

            $query->execute([
                'id' => $id,
                'id_t' => $q->getTestId(),
                'type' => $q->getType(),
                'contenu_q' => $q->getContenu()
            ]);
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }

    public function showQuestion($id) {
        $sql = "SELECT * FROM question WHERE id_q = $id";
        $db = config::getConnexion();

        try {
            $query = $db->prepare($sql);
            $query->execute();
            return $query->fetch();
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }
}
?>