<?php

namespace App\Controller;

use App\Entity\Note;
use App\Entity\User;
use DateTimeImmutable;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Entity;
use App\Repository\NoteRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class NoteController extends AbstractController
{
    #[Route('/api/notes', name: 'notes', methods:'GET')]
    public function getNoteList(NoteRepository $noteRepository, SerializerInterface $serializer): JsonResponse
    {
        $noteList = $noteRepository->findAll();
        $jsonNoteList = $serializer->serialize($noteList, 'json', ['groups' => 'getNotes']);
        return new JsonResponse($jsonNoteList, Response::HTTP_OK, [], true);
    }

    #[Route('/api/detailNote/{id}', name: 'detailNote', methods:'GET')]
    public function getNoteDetail(Note $note, SerializerInterface $serializer): JsonResponse
    {
        $jsonNoteDetail = $serializer->serialize($note, 'json', ['groups' => 'getNotes']);
        return new JsonResponse($jsonNoteDetail, Response::HTTP_OK, ['accept' => "json"], true);       
    }

    #[Route('/api/detailNote/{id}', name: 'deleteNote', methods:'DELETE')]
    public function deleteNote(Note $note, EntityManagerInterface $em):JsonResponse
    {
        $em->remove($note);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/api/notes', name: 'createNote', methods:'POST')]
    public function createNote(UserRepository $userRepository, Request $request, SerializerInterface $serializer, EntityManagerInterface $em, UrlGeneratorInterface $urlGenerator): JsonResponse
    {
        $content = $request->toArray();
        $idUser = $content['idUser'] ?? -1;


        $note = $serializer->deserialize($request->getContent(), Note::class, 'json');
        $note->setCreatedAt(new DateTimeImmutable());
        $note->setUser($userRepository->find($idUser));
        $em->persist($note);
        $em->flush();

        $jsonNote = $serializer->serialize($note, 'json', ['groups' => 'getNotes']);

        $location = $urlGenerator->generate('detailNote', ['id' => $note->getid()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonNote, Response::HTTP_CREATED, ["Location" => $location], true);
    }

    #[Route('/api/detailNote/{id}', name:'updateNote', methods:'PUT')]
    public function updateNote(Request $request, SerializerInterface $serializer, Note $currentNote, EntityManagerInterface $em, UserRepository $userRepository){
        $updatedNote = $serializer->deserialize($request->getContent(), Note::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $currentNote]);
        $content = $request->toArray();
        $idUser = $content["idUser"] ?? -1;
        
        $updatedNote->setUser($userRepository->find($idUser));
        
        $em->persist($updatedNote);
        $em->flush();

        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }
}
