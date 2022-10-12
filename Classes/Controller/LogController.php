<?php

declare(strict_types=1);

namespace MyForksFiles\T3inquisitor\Controller;

use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

/**
 * This file is part of the "T3inquisitor" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) 2022 myForksFiles <myForksFiles(at)github.com>, home
 *
 *
 *- -***
 *
 * LogController
 */
class LogController extends AbstractController
{

    /**
     * action list
     *
     * @return string|object|null|void
     */
    public function listAction()
    {
        $logs = $this->logRepository->findAll();
        $this->view->assign('logs', $logs);
    }

    /**
     * action edit
     *
     * @param \MyForksFiles\T3inquisitor\Domain\Model\Log $log
     * @TYPO3\CMS\Extbase\Annotation\IgnoreValidation("log")
     * @return string|object|null|void
     */
    public function editAction(\MyForksFiles\T3inquisitor\Domain\Model\Log $log)
    {
        $this->view->assign('log', $log);
    }

    /**
     * action update
     *
     * @param \MyForksFiles\T3inquisitor\Domain\Model\Log $log
     * @return string|object|null|void
     */
    public function updateAction(\MyForksFiles\T3inquisitor\Domain\Model\Log $log)
    {
        $this->addFlashMessage('The object was updated. Please be aware that this action is publicly accessible unless you implement an access check. See https://docs.typo3.org/p/friendsoftypo3/extension-builder/master/en-us/User/Index.html', '', \TYPO3\CMS\Core\Messaging\AbstractMessage::WARNING);
        $this->logRepository->update($log);
        $this->redirect('list');
    }

    /**
     * action delete
     *
     * @param \MyForksFiles\T3inquisitor\Domain\Model\Log $log
     * @return string|object|null|void
     */
    public function deleteAction(\MyForksFiles\T3inquisitor\Domain\Model\Log $log)
    {
        $this->addFlashMessage('The object was deleted. Please be aware that this action is publicly accessible unless you implement an access check. See https://docs.typo3.org/p/friendsoftypo3/extension-builder/master/en-us/User/Index.html', '', \TYPO3\CMS\Core\Messaging\AbstractMessage::WARNING);
        $this->logRepository->remove($log);
        $this->redirect('list');
    }
}
