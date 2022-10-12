<?php

declare(strict_types=1);

namespace MyForksFiles\T3inquisitor\Tests\Unit\Controller;

use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Extbase\Mvc\View\ViewInterface;
use TYPO3\TestingFramework\Core\AccessibleObjectInterface;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Test case
 *
 * @author myForksFiles <myForksFiles(at)github.com>
 */
class LogControllerTest extends UnitTestCase
{
    /**
     * @var \MyForksFiles\T3inquisitor\Controller\LogController|MockObject|AccessibleObjectInterface
     */
    protected $subject;

    protected function setUp(): void
    {
        parent::setUp();
        $this->subject = $this->getMockBuilder($this->buildAccessibleProxy(\MyForksFiles\T3inquisitor\Controller\LogController::class))
            ->onlyMethods(['redirect', 'forward', 'addFlashMessage'])
            ->disableOriginalConstructor()
            ->getMock();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

//    /**
//     * @test
//     */
//    public function listActionFetchesAllLogsFromRepositoryAndAssignsThemToView(): void
//    {
//        $allLogs = $this->getMockBuilder(\TYPO3\CMS\Extbase\Persistence\ObjectStorage::class)
//            ->disableOriginalConstructor()
//            ->getMock();
//
//        $logRepository = $this->getMockBuilder(\::class)
//            ->onlyMethods(['findAll'])
//            ->disableOriginalConstructor()
//            ->getMock();
//        $logRepository->expects(self::once())->method('findAll')->will(self::returnValue($allLogs));
//        $this->subject->_set('logRepository', $logRepository);
//
//        $view = $this->getMockBuilder(ViewInterface::class)->getMock();
//        $view->expects(self::once())->method('assign')->with('logs', $allLogs);
//        $this->subject->_set('view', $view);
//
//        $this->subject->listAction();
//    }

    /**
     * @test
     */
    public function editActionAssignsTheGivenLogToView(): void
    {
        $log = new \MyForksFiles\T3inquisitor\Domain\Model\Log();

        $view = $this->getMockBuilder(ViewInterface::class)->getMock();
        $this->subject->_set('view', $view);
        $view->expects(self::once())->method('assign')->with('log', $log);

        $this->subject->editAction($log);
    }

//    /**
//     * @test
//     */
//    public function updateActionUpdatesTheGivenLogInLogRepository(): void
//    {
//        $log = new \MyForksFiles\T3inquisitor\Domain\Model\Log();
//
//        $logRepository = $this->getMockBuilder(\::class)
//            ->onlyMethods(['update'])
//            ->disableOriginalConstructor()
//            ->getMock();
//
//        $logRepository->expects(self::once())->method('update')->with($log);
//        $this->subject->_set('logRepository', $logRepository);
//
//        $this->subject->updateAction($log);
//    }

//    /**
//     * @test
//     */
//    public function deleteActionRemovesTheGivenLogFromLogRepository(): void
//    {
//        $log = new \MyForksFiles\T3inquisitor\Domain\Model\Log();
//
//        $logRepository = $this->getMockBuilder(\::class)
//            ->onlyMethods(['remove'])
//            ->disableOriginalConstructor()
//            ->getMock();
//
//        $logRepository->expects(self::once())->method('remove')->with($log);
//        $this->subject->_set('logRepository', $logRepository);
//
//        $this->subject->deleteAction($log);
//    }
}
