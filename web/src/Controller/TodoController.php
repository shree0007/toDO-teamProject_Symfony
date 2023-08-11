<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Tasks;

class TodoController extends AbstractController
{
    #[Route('/todo', name: 'app_todo_list')]
    public function index(EntityManagerInterface $em)
    {
        $tasks= $em->getRepository(Tasks::class)->findBy([], ['id'=>'DESC']);
 return $this->render('todo/index.html.twig', ['tasks' => $tasks]);
    }
    #[Route('/create', name: 'create_task', methods:['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager, ManagerRegistry $doctrine):Response
    {
        // exit('to do: create a new task');
        
        $activity = trim($request->get('activity'));
        if (empty($activity) || !preg_match('/^[A-Za-z0-9, :;&\'-]+$/', $activity)) {
            $this->addFlash('error','Invalid input');
            return $this->redirectToRoute('app_todo_list');
        }
            // exit($request->get('activity'));
            $entityManager=$doctrine->getManager();
            $task = new Tasks;
            $task->setActivity($activity);
            $entityManager->persist($task);
            $entityManager->flush();
            return $this->redirectToRoute('app_todo_list');
    }
   
    #[Route('/toggle-edit/{id}', name: 'toggle_edit_task')]
    public function toggleEdit($id, EntityManagerInterface $entityManager): Response
    {
        $task = $entityManager->getRepository(Tasks::class)->find($id);
        $task->setStatus(!$task->isStatus());
        $entityManager->flush();
        return $this->redirectToRoute('app_todo_list');
    }

    #[Route('/update/{id}', name: 'update_task')]
    public function update($id, Request $request, ManagerRegistry $doctrine): Response
    {
        $entityManager = $doctrine->getManager();
        $task = $entityManager->getRepository(Tasks::class)->find($id);
    
        $activity = trim($request->get('activity'));
        if (empty($activity)) {
            return $this->redirectToRoute('app_todo_list');
        }
    
        $task->setActivity($activity);
        $task->setStatus(!$task->isStatus());
    
        $entityManager->flush();
        return $this->redirectToRoute('app_todo_list');
    }

    #[Route('/delete/{id}', name: 'delete_task')]
    public function delete($id, ManagerRegistry $doctrine):Response
    {
        $entityManager = $doctrine->getManager();
        $id = $entityManager->getRepository(Tasks::class)->find($id);
        $entityManager->remove($id);
        $entityManager->flush();
        return $this->redirectToRoute('app_todo_list');
    }
}
