<?php
declare(strict_types=1);

namespace acoby\controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * An interface showing all possible functions in CRUD.
 */
interface RestController {
  /**
   * @param ServerRequestInterface $request
   * @param ResponseInterface $response
   * @param array $args
   * @return ResponseInterface
   */
  public function create(ServerRequestInterface $request, ResponseInterface $response, array $args) :ResponseInterface;

  /**
   * @param ServerRequestInterface $request
   * @param ResponseInterface $response
   * @param array $args
   * @return ResponseInterface
   */
  public function update(ServerRequestInterface $request, ResponseInterface $response, array $args) :ResponseInterface;

  /**
   * @param ServerRequestInterface $request
   * @param ResponseInterface $response
   * @param array $args
   * @return ResponseInterface
   */
  public function delete(ServerRequestInterface $request, ResponseInterface $response, array $args) :ResponseInterface;

  /**
   * @param ServerRequestInterface $request
   * @param ResponseInterface $response
   * @param array $args
   * @return ResponseInterface
   */
  public function get(ServerRequestInterface $request, ResponseInterface $response, array $args) :ResponseInterface;

  /**
   * @param ServerRequestInterface $request
   * @param ResponseInterface $response
   * @param array $args
   * @return ResponseInterface
   */
  public function list(ServerRequestInterface $request, ResponseInterface $response, array $args) :ResponseInterface;

  /**
   * @param ServerRequestInterface $request
   * @param ResponseInterface $response
   * @param array $args
   * @return ResponseInterface
   */
  public function search(ServerRequestInterface $request, ResponseInterface $response, array $args) :ResponseInterface;
}