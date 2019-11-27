<?php

namespace Vidal\DrugBundle\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;

class TagTotalService
{
	private $container;

	public function __construct(ContainerInterface $container)
	{
		$this->container = $container;
	}

	public function count($tagId)
	{
		$em  = $this->container->get('doctrine')->getManager('drug');
		$tag = $em->getRepository('VidalDrugBundle:Tag')->findOneById($tagId);
		$pdo = $em->getConnection();

		if ($tag === null) {
			return 0;
		}

		$artIds         = array();
		$articleIds     = array();
		$publicationIds = array();

		# tag.arts
		$stmt = $pdo->prepare("SELECT a.id FROM art a JOIN art_tag at ON at.art_id = a.id WHERE at.tag_id = $tagId");
		$stmt->execute();
		$arts = $stmt->fetchAll();

		foreach ($arts as $art) {
			$artIds[] = $art['id'];
		}

		# tag.articles
		$stmt = $pdo->prepare("SELECT a.id FROM article a JOIN article_tag at ON at.article_id = a.id WHERE at.tag_id = $tagId");
		$stmt->execute();
		$articles = $stmt->fetchAll();

		foreach ($articles as $article) {
			$articleIds[] = $article['id'];
		}

		# tag.publications
		$stmt = $pdo->prepare("SELECT p.id FROM publication p JOIN publication_tag pt ON pt.publication_id = p.id WHERE pt.tag_id = $tagId");
		$stmt->execute();
		$publications = $stmt->fetchAll();

		foreach ($publications as $publication) {
			$publicationIds[] = $publication['id'];
		}

		# tag.infoPage.arts
		$stmt = $pdo->prepare("SELECT a.id FROM art a JOIN art_infopage ai ON ai.art_id = a.id JOIN infopage i ON i.InfoPageID = ai.InfoPageID WHERE i.tag_id = $tagId");
		$stmt->execute();
		$arts = $stmt->fetchAll();

		foreach ($arts as $art) {
			$artIds[] = $art['id'];
		}

		# tag.infoPage.articles
		$stmt = $pdo->prepare("SELECT a.id FROM article a JOIN article_infopage ai ON ai.article_id = a.id JOIN infopage i ON i.InfoPageID = ai.InfoPageID WHERE i.tag_id = $tagId");
		$stmt->execute();
		$articles = $stmt->fetchAll();

		foreach ($articles as $article) {
			$articleIds[] = $article['id'];
		}

		# tag.infoPage.publications
		$stmt = $pdo->prepare("SELECT p.id FROM publication p JOIN publication_infopage pi ON pi.publication_id = p.id JOIN infopage i ON i.InfoPageID = pi.InfoPageID WHERE i.tag_id = $tagId");
		$stmt->execute();
		$publications = $stmt->fetchAll();

		foreach ($publications as $publication) {
			$publicationIds[] = $publication['id'];
		}

		$total = count(array_unique($artIds)) + count(array_unique($articleIds)) + count(array_unique($publicationIds));

		# приходится через PDO, так как в PostUpdate событии нельзя обновлять записи в базе данных
		$pdo->prepare("UPDATE tag SET total = $total WHERE id = $tagId")->execute();

		return $total;
	}
}