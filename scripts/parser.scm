#!/run/current-system/profile/bin/guile
!#

(use-modules (sxml simple))
(use-modules (ice-9 pretty-print))

(define verbose #t)
;(define verbose #f)

(define verbose-display
  (lambda (thing)
	(if verbose
		(display thing))))

(define process-response
  (lambda (response)
	"doc for process-response"
	(let ((root (car response)))
	 (if (eq? root 'response)
		 (begin
			(process-result (cdr response)))))))

(define get-data
  (lambda (thing)
	(if (list? thing)
		(get-data (car thing))
		(if (symbol? thing)
			(symbol->string thing)
			thing))))

(define print-data
  (lambda (data filter outer)
	(when (eq? data filter)
	  (let ((check (cdr outer)))
		(when (and (list? check)
				   (> (length check) 0))
		  (verbose-display (symbol->string data))
		  (verbose-display "=")
		  (verbose-display (car (cdr outer)))
		  (verbose-display "\n")))
	  )))

(define extract-project-info
  (lambda (project)
	"asdf"
	(verbose-display "\n-parsing project data-\n")
	;; cdr through to find the elements we need
	(let ((cur (cdr project)))
	  (while (and (list? cur)
				  (> (length cur) 0))
		(when (list? cur)
		  (let ((inner (car cur)))
			(when (list? inner)
			  (let ((sym (car inner)))
				(print-data sym 'id inner)
				(print-data sym 'name inner)
				(print-data sym 'description inner)
				(print-data sym 'homepage_url inner)
				;; licenses
				;; links
				))))
		(set! cur (cdr cur))))))

(define iterate-result-data
  (lambda (result)
	"doc"
	;;(verbose-display "\n-iterating result data-\n")
	;; cdr through result - every top level list is a project
	(when (> (length result) 0)
	  (let ((child (cdr result)))
		(when (and (list? child)
				   (> (length child) 0))
		  (if (list? (car child))
			(extract-project-info (car child))))))))

(define extract-result-data
  (lambda (result)
	"doc"
	;; (verbose-display "\n-extracting result data-\n")
	(iterate-result-data result)
	(when (and (list? result)
			   (> (length result) 0))
	  (extract-result-data (cdr result)))))

(define process-result
  (lambda (result)
	"doc for process-result"
	(if (and (list? result)
			 (> (length result) 0))
		(begin
		  ; check if the first item is the symbol root
		  (if (and (symbol? (car result))
				   (eq? (car result) 'result))
			  (begin
				(verbose-display "found result\n")
				(extract-result-data result)
				)
			  (begin
				(process-result (car result))
				(process-result (cdr result))))))))

(define iterate-data
  (lambda (data)
	"doc for iterate-data"
	(do ((i 1 (1+ i)))
		((and (list? data) (> i (- (length data) 1))))
	  (let ((tmp (list-ref data i)))
		(process-response tmp)))))

(define data-port (open-file "/home/niebie/sc/560-project/scripts/sample-data.xml" "r"))
(define sxml-data (xml->sxml data-port))

;; code to save output file
;; (define output-port (open-file "/home/niebie/sc/560-project/scripts/sample-data.scm" "w"))
;; (write sxml-data output-port)

(verbose-display "\noutput:\n")
(iterate-data sxml-data)

