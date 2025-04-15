<?php

function generatePagination($currentPage, $totalPages, $baseUrl, $search = '') {
	$paginationHTML = '';
	$queryString = $search ? '&search=' . urlencode($search) : '';

	if ($totalPages > 1) {
		$paginationHTML .= '<div class="pagination">';

		if ($currentPage > 1) {
			$paginationHTML .= '<a href="' . $baseUrl . '?page=' . ($currentPage - 1) . $queryString . '">Previous</a>';
		} else {
			$paginationHTML .= '<span class="disabled">Previous</span>';
		}

		if ($totalPages <= 10) {
			for ($i = 1; $i <= $totalPages; $i++) {
				if ($i == $currentPage) {
					$paginationHTML .= '<span class="current">' . $i . '</span>';
				} else {
					$paginationHTML .= '<a href="' . $baseUrl . '?page=' . $i . $queryString . '">' . $i . '</a>';
				}
			}
		} else {
			if ($currentPage <= 5) {
				for ($i = 1; $i <= 7; $i++) {
					if ($i == $currentPage) {
						$paginationHTML .= '<span class="current">' . $i . '</span>';
					} else {
						$paginationHTML .= '<a href="' . $baseUrl . '?page=' . $i . $queryString . '">' . $i . '</a>';
					}
				}
				$paginationHTML .= '<span>...</span>';
				$paginationHTML .= '<a href="' . $baseUrl . '?page=' . $totalPages . $queryString . '">' . $totalPages . '</a>';
			} elseif ($currentPage > $totalPages - 5) {
				$paginationHTML .= '<a href="' . $baseUrl . '?page=1' . $queryString . '">1</a>';
				$paginationHTML .= '<span>...</span>';
				for ($i = $totalPages - 6; $i <= $totalPages; $i++) {
					if ($i == $currentPage) {
						$paginationHTML .= '<span class="current">' . $i . '</span>';
					} else {
						$paginationHTML .= '<a href="' . $baseUrl . '?page=' . $i . $queryString . '">' . $i . '</a>';
					}
				}
			} else {
				$paginationHTML .= '<a href="' . $baseUrl . '?page=1' . $queryString . '">1</a>';
				$paginationHTML .= '<span>...</span>';
				for ($i = $currentPage - 3; $i <= $currentPage + 3; $i++) {
					if ($i == $currentPage) {
						$paginationHTML .= '<span class="current">' . $i . '</span>';
					} else {
						$paginationHTML .= '<a href="' . $baseUrl . '?page=' . $i . $queryString . '">' . $i . '</a>';
					}
				}
				$paginationHTML .= '<span>...</span>';
				$paginationHTML .= '<a href="' . $baseUrl . '?page=' . $totalPages . $queryString . '">' . $totalPages . '</a>';
			}
		}

		if ($currentPage < $totalPages) {
			$paginationHTML .= '<a href="' . $baseUrl . '?page=' . ($currentPage + 1) . $queryString . '">Next</a>';
		} else {
			$paginationHTML .= '<span class="disabled">Next</span>';
		}

		$paginationHTML .= '</div>';
	}

	return $paginationHTML;
}
